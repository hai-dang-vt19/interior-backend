<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Repositories\SiteOrder\SiteOrderRepositoryInterface;
use App\Support\CustomerOrderNotifier;
use App\Support\Vnpay\VnpaySignature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class VnpayCallbackService
{
    public function __construct(
        private SiteOrderRepositoryInterface $siteOrderRepository
    ) {}

    /** Trình duyệt khách quay về sau khi thanh toán tại VNPay. */
    public function handleReturn(Request $request, int $customerId): RedirectResponse
    {
        $params = self::extractVnpParamsFromRequest($request);
        $hash = $request->query('vnp_SecureHash');
        if (! VnpaySignature::verify($params, $hash !== null ? (string) $hash : null, (string) config('vnpay.hash_secret'))) {
            return redirect()->route('site.orders.index')->with('dataError', 'Xác thực giao dịch VNPay không hợp lệ.');
        }

        $txnRef = (string) ($params['vnp_TxnRef'] ?? '');
        $scaled = (int) ($params['vnp_Amount'] ?? 0);
        $paidByGateway = (($params['vnp_ResponseCode'] ?? '') === '00');
        $vnpTran = isset($params['vnp_TransactionNo']) ? (string) $params['vnp_TransactionNo'] : null;

        $sync = DB::transaction(function () use ($txnRef, $scaled, $paidByGateway, $vnpTran, $customerId): array {
            $locked = $this->siteOrderRepository->lockOrderByVnpTxnRef($txnRef);
            if ($locked === null) {
                throw new \RuntimeException('Không tìm thấy đơn hàng tương ứng giao dịch VNPay.');
            }
            if ((int) $locked->customer_id !== $customerId) {
                throw new \RuntimeException('Đơn hàng không thuộc tài khoản hiện tại.');
            }

            return $this->siteOrderRepository->finalizeVnpayOnlinePayment(
                $locked,
                $scaled,
                $paidByGateway,
                $vnpTran
            );
        });

        $rsp = (string) ($sync['rsp_code'] ?? '');
        if ($rsp !== '00') {
            $msg = match ($rsp) {
                '04' => 'Số tiền giao dịch VNPay không khớp đơn hàng. Vui lòng liên hệ hỗ trợ.',
                default => 'Không đồng bộ được trạng thái thanh toán.',
            };
            $orderErr = Order::query()->where('vnp_txn_ref', $txnRef)->first();

            return $orderErr !== null
                ? redirect()->route('site.orders.show', $orderErr->id)->with('dataError', $msg)
                : redirect()->route('site.orders.index')->with('dataError', $msg);
        }

        $becamePaid = (bool) ($sync['became_paid'] ?? false);

        /** @var Order $orderShown */
        $orderShown = Order::query()->where('vnp_txn_ref', $txnRef)->firstOrFail();

        if ($becamePaid) {
            CustomerOrderNotifier::sendOrderUpdatedEmail($orderShown, CustomerOrderNotifier::CONTEXT_VNPAY_PAID);
        }

        $status = $orderShown->fresh()->payment_status ?? PaymentStatus::PENDING;

        return match ($status) {
            PaymentStatus::PAID => redirect()->route('site.orders.show', $orderShown->id)
                ->with('dataSuccess', 'Thanh toán VNPay thành công.'),
            PaymentStatus::FAILED => redirect()->route('site.orders.show', $orderShown->id)
                ->with('dataError', 'Giao dịch VNPay không thành công. Vui lòng đặt lại hoặc chọn phương thức khác.'),
            default => redirect()->route('site.orders.show', $orderShown->id)
                ->with('dataError', 'Chưa ghi nhận thanh toán. Vui lòng chờ ít phút hoặc liên hệ hỗ trợ.'),
        };
    }

    /** Server VNPay gọi IPN (không session khách). */
    public function handleIpn(Request $request): JsonResponse
    {
        $params = self::extractVnpParamsFromRequest($request);
        $hash = $request->query('vnp_SecureHash');
        if (! VnpaySignature::verify($params, $hash !== null ? (string) $hash : null, (string) config('vnpay.hash_secret'))) {
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
        }

        $txnRef = (string) ($params['vnp_TxnRef'] ?? '');
        $scaled = (int) ($params['vnp_Amount'] ?? 0);

        $responseCode = (string) ($params['vnp_ResponseCode'] ?? '');
        $txnStatus = (string) ($params['vnp_TransactionStatus'] ?? '');
        $paidByGateway = $responseCode === '00' && $txnStatus === '00';
        $vnpTran = isset($params['vnp_TransactionNo']) ? (string) $params['vnp_TransactionNo'] : null;

        try {
            $becamePaid = false;
            DB::transaction(function () use (
                $txnRef,
                $scaled,
                $paidByGateway,
                $vnpTran,
                &$becamePaid
            ): void {
                $locked = $this->siteOrderRepository->lockOrderByVnpTxnRef($txnRef);
                if ($locked === null) {
                    throw new \RuntimeException('not_found');
                }

                $sync = $this->siteOrderRepository->finalizeVnpayOnlinePayment(
                    $locked,
                    $scaled,
                    $paidByGateway,
                    $vnpTran
                );

                $code = (string) ($sync['rsp_code'] ?? '');
                if ($code === '01') {
                    throw new \RuntimeException('not_found');
                }
                if ($code === '04') {
                    throw new \RuntimeException('bad_amount');
                }
                if ($code !== '00') {
                    throw new \RuntimeException('unknown');
                }

                $becamePaid = (bool) ($sync['became_paid'] ?? false);
            });
        } catch (\RuntimeException $e) {
            return match ($e->getMessage()) {
                'not_found' => response()->json(['RspCode' => '01', 'Message' => 'Order not found']),
                'bad_amount' => response()->json(['RspCode' => '04', 'Message' => 'invalid amount']),
                'unknown' => response()->json(['RspCode' => '99', 'Message' => 'Unknown error']),
                default => response()->json(['RspCode' => '99', 'Message' => 'Unknown error']),
            };
        }

        if ($becamePaid) {
            $orderFresh = Order::query()->where('vnp_txn_ref', $txnRef)->first();
            if ($orderFresh !== null) {
                CustomerOrderNotifier::sendOrderUpdatedEmail($orderFresh, CustomerOrderNotifier::CONTEXT_VNPAY_PAID);
            }
        }

        return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
    }

    /** @return array<string, string> */
    private static function extractVnpParamsFromRequest(Request $request): array
    {
        $out = [];
        foreach ($request->query() as $key => $value) {
            if (! str_starts_with((string) $key, 'vnp_')) {
                continue;
            }
            if (is_scalar($value) || $value instanceof \Stringable) {
                $out[(string) $key] = (string) $value;
            }
        }

        return $out;
    }
}
