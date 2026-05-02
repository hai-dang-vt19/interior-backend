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
    $customerCanCancel = in_array($order->status, [OrderStatus::PENDING, OrderStatus::CONFIRMED], true);
@endphp

@section('content')
    <section class="ordd-page">
        <header class="ordd-head mb-3">
            <div>
                <h1 class="ordd-title mb-1">Chi tiết đơn #{{ $order->order_code ?? $order->id }}</h1>
                <p class="ordd-sub mb-0">Theo dõi tiến độ xử lý và thông tin giao hàng của đơn.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if ($customerCanCancel)
                    <form
                        action="{{ route('site.orders.cancel', $order->id) }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirm('Bạn chắc chắn muốn huỷ đơn? Đơn sẽ chuyển sang đã huỷ và số lượng đặt được nhập lại kho.')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">Huỷ đơn</button>
                    </form>
                @endif
                <form action="{{ route('site.orders.reorder', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-dark">Mua lai</button>
                </form>
                <a href="{{ route('site.orders.index') }}" class="btn btn-sm btn-outline-dark">Quay lại danh sách</a>
            </div>
        </header>

        <div class="ordd-summary mb-3">
            <div class="ordd-summary-item">
                <small>Trạng thái</small>
                <span class="site-badge {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>
            <div class="ordd-summary-item">
                <small>Thanh toán</small>
                <span class="site-badge {{ $paymentClass }}">{{ $paymentLabel }}</span>
            </div>
            <div class="ordd-summary-item">
                <small>Phương thức</small>
                <strong>{{ $methodLabel }}</strong>
            </div>
            <div class="ordd-summary-item">
                <small>Ngày đặt</small>
                <strong>{{ $order->created_at?->format('d/m/Y H:i') }}</strong>
            </div>
            <div class="ordd-summary-item ordd-summary-item--wide">
                <small>Địa chỉ nhận</small>
                <strong>{{ $order->shipping_address }}</strong>
            </div>
            <div class="ordd-summary-item">
                <small>SĐT nhận</small>
                <strong>{{ $order->shipping_phone }}</strong>
            </div>
            @if ($order->notes)
                <div class="ordd-summary-item ordd-summary-item--wide">
                    <small>Ghi chú đơn</small>
                    <strong>{{ $order->notes }}</strong>
                </div>
            @endif
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

        <div class="ordd-table-wrap">
            <div class="table-responsive">
                <table class="table align-middle mb-0 ordd-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th width="120">Đơn giá</th>
                            <th width="100">Số lượng</th>
                            <th width="140">Thành tiền</th>
                            <th width="130">Đánh giá</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            @php($line = ((float) $item->price) * ((int) $item->quantity))
                            <tr>
                                <td>
                                    <strong class="d-block">{{ $item->product?->name ?? 'Sản phẩm đã gỡ' }}</strong>
                                    @include('site.component.line-pricing-note', [
                                        'product' => $item->product,
                                        'variant' => $item->productVariant,
                                        'storedUnit' => $item->price,
                                        'orderLinePreview' => true,
                                    ])
                                </td>
                                <td>{{ number_format((float) $item->price, 0, ',', '.') }} đ</td>
                                <td>{{ (int) $item->quantity }}</td>
                                <td><strong>{{ number_format($line, 0, ',', '.') }} đ</strong></td>
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
                            <th class="text-danger">{{ number_format((float) $order->total_amount, 0, ',', '.') }} đ</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </section>
@endsection
