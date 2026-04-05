@extends('site.base')

@php
    use App\Enums\OrderStatus;
    $statusClass = match ($order->status?->value) {
        1 => 'site-badge-status-pending',
        2 => 'site-badge-status-confirmed',
        3 => 'site-badge-status-shipping',
        4 => 'site-badge-status-delivered',
        5 => 'site-badge-status-cancelled',
        default => 'site-badge-neutral'
    };
    $paymentClass = match ($order->payment_status?->value) {
        1 => 'site-badge-status-pending',
        2 => 'site-badge-status-paid',
        3 => 'site-badge-status-failed',
        default => 'site-badge-neutral'
    };
    $currentStatus = (int) ($order->status?->value ?? 1);
    $statusLabel = match ($order->status?->value) {
        1 => 'Chờ xác nhận',
        2 => 'Đã xác nhận',
        3 => 'Đang giao',
        4 => 'Đã giao',
        5 => 'Đã hủy',
        default => 'Không xác định'
    };
    $paymentLabel = match ($order->payment_status?->value) {
        1 => 'Chưa thanh toán',
        2 => 'Đã thanh toán',
        3 => 'Thanh toán lỗi',
        default => 'Không xác định'
    };
    $methodLabel = match ($order->payment_method?->value) {
        1 => 'Tiền mặt',
        2 => 'COD',
        3 => 'Chuyển khoản',
        4 => 'Ví điện tử',
        default => 'Không xác định'
    };
    $steps = [
        1 => 'Chờ xác nhận',
        2 => 'Đã xác nhận',
        3 => 'Đang giao',
        4 => 'Đã giao',
        5 => 'Đã hủy',
    ];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0 site-section-title">Chi tiết đơn #{{ $order->id }}</h4>
    <a href="{{ route('site.orders.index') }}" class="btn btn-sm btn-outline-dark">Quay lại danh sách</a>
</div>

<div class="row g-3 mb-3 card site-panel p-3">
    <div class="col-md-4"><strong>Trạng thái:</strong> <span class="site-badge {{ $statusClass }}">{{ $statusLabel }}</span></div>
    <div class="col-md-4"><strong>Thanh toán:</strong> <span class="site-badge {{ $paymentClass }}">{{ $paymentLabel }}</span></div>
    <div class="col-md-4"><strong>Phương thức:</strong> {{ $methodLabel }}</div>
    <div class="col-md-6"><strong>Địa chỉ nhận:</strong> {{ $order->shipping_address }}</div>
    <div class="col-md-3"><strong>SĐT nhận:</strong> {{ $order->shipping_phone }}</div>
    <div class="col-md-3"><strong>Ngày đặt:</strong> {{ $order->created_at?->format('d/m/Y H:i') }}</div>
</div>

<div class="card site-panel p-3 mb-3">
    <div class="site-timeline">
        @foreach ($steps as $stepValue => $stepLabel)
            @php($isActive = $currentStatus === 5 ? $stepValue === 5 : $stepValue <= $currentStatus && $stepValue !== 5)
            <div class="site-timeline-item {{ $isActive ? 'active' : '' }}">
                <div class="site-timeline-dot"></div>
                <div class="site-timeline-label">{{ $stepLabel }}</div>
            </div>
        @endforeach
    </div>
</div>

<div class="table-responsive site-table-wrap">
    <table class="table table-bordered align-middle">
        <thead>
            <tr class="table-light">
                <th>Sản phẩm</th>
                <th width="120">Đơn giá</th>
                <th width="100">Số lượng</th>
                <th width="140">Thành tiền</th>
                <th width="130">Đánh giá</th>
            </tr>
        </thead>
        <tbody>
            @php($total = 0)
            @foreach ($order->items as $item)
                @php($line = ((float) $item->price) * ((int) $item->quantity))
                @php($total += $line)
                <tr>
                    <td>{{ $item->product?->name }}</td>
                    <td>{{ number_format((float) $item->price, 0, ',', '.') }} đ</td>
                    <td>{{ (int) $item->quantity }}</td>
                    <td>{{ number_format($line, 0, ',', '.') }} đ</td>
                    <td>
                        @if ($order->status === OrderStatus::DELIVERED && $item->product_id)
                            <a href="{{ route('site.products.show', $item->product_id) }}#product-reviews">Viết đánh giá</a>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Tổng cộng</th>
                <th class="text-danger">{{ number_format($total, 0, ',', '.') }} đ</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
