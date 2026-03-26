@extends('site.base')

@section('content')
<h4 class="site-section-title site-page-head">Đơn hàng của tôi</h4>

@if ($orders->isEmpty())
    @include('site.component.empty-state', [
        'title' => 'Bạn chưa có đơn hàng nào',
        'description' => 'Bắt đầu mua sắm để tạo đơn hàng đầu tiên.',
        'actionUrl' => route('site.home'),
        'actionText' => 'Mua sắm ngay',
    ])
@else
    <div class="table-responsive site-table-wrap">
        <table class="table table-striped table-bordered align-middle">
            <thead>
                <tr class="table-light">
                    <th>Mã đơn</th>
                    <th>Ngày đặt</th>
                    <th>Số SP</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Thanh toán</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    @php($statusLabel = match ($order->status?->value) {
                        1 => 'Chờ xác nhận',
                        2 => 'Đã xác nhận',
                        3 => 'Đang giao',
                        4 => 'Đã giao',
                        5 => 'Đã hủy',
                        default => 'Không xác định'
                    })
                    @php($paymentLabel = match ($order->payment_status?->value) {
                        1 => 'Chưa thanh toán',
                        2 => 'Đã thanh toán',
                        3 => 'Thanh toán lỗi',
                        default => 'Không xác định'
                    })
                    @php($statusClass = match ($order->status?->value) {
                        1 => 'site-badge-status-pending',
                        2 => 'site-badge-status-confirmed',
                        3 => 'site-badge-status-shipping',
                        4 => 'site-badge-status-delivered',
                        5 => 'site-badge-status-cancelled',
                        default => 'site-badge-neutral'
                    })
                    @php($paymentClass = match ($order->payment_status?->value) {
                        1 => 'site-badge-status-pending',
                        2 => 'site-badge-status-paid',
                        3 => 'site-badge-status-failed',
                        default => 'site-badge-neutral'
                    })
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $order->items->count() }}</td>
                        <td>{{ number_format((float) $order->total_amount, 0, ',', '.') }} đ</td>
                        <td><span class="site-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                        <td><span class="site-badge {{ $paymentClass }}">{{ $paymentLabel }}</span></td>
                        <td>
                            <a href="{{ route('site.orders.show', $order->id) }}" class="btn btn-sm btn-outline-dark">Chi tiết</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $orders->links() }}</div>
@endif
@endsection
