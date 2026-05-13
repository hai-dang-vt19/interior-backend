<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Support\Vnpay\VnpaySignature;
use Carbon\Carbon;
use InvalidArgumentException;

final class VnpayPaymentService
{
    /** URL cổng VNPay (redirect trình duyệt khách). */
    public function paymentRedirectUrl(Order $order, string $clientIp): string
    {
        $tmn = (string) config('vnpay.tmn_code');
        $secret = (string) config('vnpay.hash_secret');
        if ($tmn === '' || $secret === '') {
            throw new InvalidArgumentException('Cấu hình VNPay (TMN / Hash Secret) chưa được thiết lập.');
        }

        if ($order->payment_method !== PaymentMethod::VNPAY) {
            throw new InvalidArgumentException('Đơn không áp dụng thanh toán VNPay.');
        }

        $txnRef = (string) ($order->vnp_txn_ref ?? '');
        if ($txnRef === '') {
            throw new InvalidArgumentException('Thiếu mã thanh toán VNPay (vnp_txn_ref).');
        }

        $baseUrl = rtrim((string) config('vnpay.payment_url'));
        $amountScaled = (int) round(((float) $order->total_amount) * 100);
        $nowTz = Carbon::now('Asia/Ho_Chi_Minh');
        $expire = $nowTz->copy()->addMinutes(max(1, (int) config('vnpay.expire_minutes')));

        $info = mb_substr('Thanh toan don '.($order->order_code ?? (string) $order->id), 0, 240);

        $inputData = [
            'vnp_Version' => (string) config('vnpay.version'),
            'vnp_Command' => (string) config('vnpay.command'),
            'vnp_TmnCode' => $tmn,
            'vnp_Amount' => (string) $amountScaled,
            'vnp_CreateDate' => $nowTz->format('YmdHis'),
            'vnp_CurrCode' => (string) config('vnpay.curr_code'),
            'vnp_IpAddr' => $clientIp !== '' ? $clientIp : '127.0.0.1',
            'vnp_Locale' => (string) config('vnpay.locale'),
            'vnp_OrderInfo' => $info,
            'vnp_OrderType' => 'other',
            'vnp_ReturnUrl' => route('site.payment.vnpay.return'),
            'vnp_TxnRef' => $txnRef,
            'vnp_ExpireDate' => $expire->format('YmdHis'),
        ];

        return VnpaySignature::buildPaymentRedirectUrl($baseUrl, $inputData, $secret);
    }
}
