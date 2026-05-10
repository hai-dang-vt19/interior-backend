<x-mail::message>
Xin chào **{{ $order->customer?->full_name ?? 'Quý khách' }}**,

{{ $introLine }}

---

- **Mã đơn:** {{ $order->order_code ?: '#'.$order->id }}
- **Trạng thái đơn:** {{ $statusVi }}
- **Thanh toán:** {{ $paymentMethodVi }} — {{ $paymentStatusVi }}
- **Tổng tiền:** {{ number_format((float) $order->total_amount, 0, ',', '.') }} đ
@if ((int) $order->loyalty_discount_amount > 0)
@php
    $tierLbl = $order->loyaltyTierSnapshotLabel();
    $pct = $order->loyaltyTierPercentSnapshot();
    $loyaltyMailSuffix = '';
    if ($tierLbl) {
        $loyaltyMailSuffix = $tierLbl;
    }
    if ($pct !== null && $pct > 0) {
        $loyaltyMailSuffix .= ($loyaltyMailSuffix !== '' ? ' ' : '').$pct.'%';
    }
@endphp
- **Đã giảm (hạng thành viên{{ $loyaltyMailSuffix !== '' ? ' — '.$loyaltyMailSuffix : '' }}):** {{ number_format((int) $order->loyalty_discount_amount, 0, ',', '.') }} đ
@endif
- **Địa chỉ giao hàng:** {{ $order->shipping_address }}
- **Điện thoại liên hệ:** {{ $order->shipping_phone }}
@if (filled($order->shipping_provider))
- **Đơn vị vận chuyển:** {{ $order->shipping_provider }}
@endif
@if (filled($order->tracking_number))
- **Mã vận đơn:** {{ $order->tracking_number }}
@endif

@if ($order->items->isNotEmpty())
**Một số sản phẩm trong đơn:**
@foreach ($order->items->take(8) as $line)
- {{ $line->product?->name ?? 'Sản phẩm' }} × {{ (int) $line->quantity }} — {{ number_format((float) $line->price * (int) $line->quantity, 0, ',', '.') }} đ
@endforeach
@if ($order->items->count() > 8)
- … và {{ $order->items->count() - 8 }} dòng khác (xem đầy đủ trên website).
@endif
@endif

<x-mail::button :url="$orderDetailUrl" color="primary">
    Xem chi tiết đơn hàng
</x-mail::button>

Nếu bạn không thực hiện thay đổi nào mà nhận được email, vui lòng liên hệ cửa hàng ngay.

Trân trọng,<br>
{{ config('app.name') }}

<x-mail::subcopy>
Nếu nút không hoạt động, mở liên kết: [{{ $orderDetailUrl }}]({{ $orderDetailUrl }})
</x-mail::subcopy>
</x-mail::message>
