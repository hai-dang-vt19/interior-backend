<?php

declare(strict_types=1);

namespace App\Support;

use App\Mail\CustomerOrderUpdatedMail;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Gửi email thông báo khách khi đơn hàng thay đổi (không chặn luồng nghiệp vụ nếu SMTP lỗi).
 */
final class CustomerOrderNotifier
{
    /** Tạo đơn mới từ trang quản trị */
    public const CONTEXT_ADMIN_CREATED = 'admin_created';

    /** Cập nhật toàn bộ đơn từ trang quản trị */
    public const CONTEXT_ADMIN_FULL = 'admin_full';

    /** Chỉ cập nhật vận chuyển / trạng thái vận hành từ panel giao hàng */
    public const CONTEXT_ADMIN_SHIPPING = 'admin_shipping';

    /** Khách huỷ đơn trên website */
    public const CONTEXT_CUSTOMER_CANCEL = 'customer_cancel';

    /** Thanh toán VNPay thành công (IPN / return) */
    public const CONTEXT_VNPAY_PAID = 'vnpay_paid';

    public static function sendOrderUpdatedEmail(Order $order, string $context): void
    {
        $order->loadMissing('customer');

        try {
            $customer = $order->customer;
            if ($customer === null) {
                return;
            }

            $email = trim((string) $customer->email);
            if ($email === '') {
                return;
            }

            $snapshot = Order::query()
                ->with(['items.product', 'customer'])
                ->find($order->getKey());
            if ($snapshot === null) {
                return;
            }

            Mail::to($email)->send(new CustomerOrderUpdatedMail($snapshot, $context));
        } catch (\Throwable $e) {
            Log::warning('Gửi mail thông báo cập nhật đơn không thành công', [
                'order_id' => $order->id,
                'context' => $context,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
