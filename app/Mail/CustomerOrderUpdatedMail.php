<?php

declare(strict_types=1);

namespace App\Mail;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Support\CustomerOrderNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerOrderUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $updateContext  Một trong các hằng {@see \App\Support\CustomerOrderNotifier}
     */
    public function __construct(
        public Order $order,
        public string $updateContext
    ) {}

    public function envelope(): Envelope
    {
        $suffix = match ($this->updateContext) {
            CustomerOrderNotifier::CONTEXT_ADMIN_SHIPPING => ' (giao hàng)',
            CustomerOrderNotifier::CONTEXT_CUSTOMER_CANCEL => ' (huỷ đơn)',
            CustomerOrderNotifier::CONTEXT_VNPAY_PAID => ' (thanh toán)',
            default => '',
        };

        $code = $this->order->order_code !== null && $this->order->order_code !== ''
            ? (string) $this->order->order_code
            : '#'.$this->order->id;

        return new Envelope(
            subject: 'Cập nhật đơn hàng '.$code.' — '.config('app.name').$suffix,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.customer-order-updated',
            with: [
                'introLine' => $this->resolveIntroLine(),
                'orderDetailUrl' => route('site.orders.show', ['id' => $this->order->id]),
                'statusVi' => $this->vietnameseOrderStatus(),
                'paymentMethodVi' => $this->vietnamesePaymentMethod(),
                'paymentStatusVi' => $this->vietnamesePaymentStatus(),
            ],
        );
    }

    /** @return array<int, \Illuminate\Mail\Mailables\Attachment> */
    public function attachments(): array
    {
        return [];
    }

    private function resolveIntroLine(): string
    {
        return match ($this->updateContext) {
            CustomerOrderNotifier::CONTEXT_ADMIN_SHIPPING => 'Cửa hàng vừa cập nhật thông tin giao hàng hoặc tiến độ xử lý đơn của bạn. Chi tiết như sau:',
            CustomerOrderNotifier::CONTEXT_CUSTOMER_CANCEL => 'Chúng tôi đã ghi nhận yêu cầu huỷ đơn. Trạng thái đơn và tổng tiền tham khảo trong bảng bên dưới.',
            CustomerOrderNotifier::CONTEXT_VNPAY_PAID => 'Hệ thống đã ghi nhận thanh toán VNPay thành công cho đơn của bạn. Chi tiết đơn hàng và trạng thái:',
            default => 'Đơn hàng của bạn vừa được cập nhật trên hệ thống. Vui lòng kiểm tra lại thông tin và trạng thái thanh toán.',
        };
    }

    private function vietnameseOrderStatus(): string
    {
        return (string) ($this->order->status?->label() ?? '—');
    }

    private function vietnamesePaymentStatus(): string
    {
        return (string) ($this->order->payment_status?->label() ?? '—');
    }

    private function vietnamesePaymentMethod(): string
    {
        $m = $this->order->payment_method;

        return match ($m) {
            PaymentMethod::CASH => 'Tiền mặt',
            PaymentMethod::COD => 'Thu hộ (COD)',
            PaymentMethod::BANK_TRANSFER => 'Chuyển khoản',
            PaymentMethod::E_WALLET => 'Ví điện tử',
            PaymentMethod::VNPAY => 'VNPay',
            default => (string) ($m?->label() ?? '—'),
        };
    }
}
