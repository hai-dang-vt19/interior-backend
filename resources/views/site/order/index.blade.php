@extends('site.base')

@section('content')
    <section class="ord-page">
        <header class="ord-head mb-3">
            <h1 class="ord-title mb-1">Đơn hàng của tôi</h1>
            <p class="ord-sub mb-0">Theo dõi trạng thái xử lý đơn và lịch sử mua sắm của bạn.</p>
        </header>

        <form method="GET" action="{{ route('site.orders.index') }}" class="ord-filter mb-3">
            <label for="ord_status" class="form-label mb-0">Lọc theo trạng thái đơn</label>
            <select id="ord_status" name="status" class="form-select">
                <option value="0" {{ ((int) ($status ?? 0)) === 0 ? 'selected' : '' }}>Tất cả</option>
                @foreach (App\Enums\OrderStatus::cases() as $st)
                    <option value="{{ $st->value }}" {{ ((int) ($status ?? 0)) === $st->value ? 'selected' : '' }}>
                        {{ $st->label() }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline-dark btn-sm">Áp dụng</button>
        </form>

        @if ($orders->isEmpty())
            @include('site.component.empty-state', [
                'title' => 'Bạn chưa có đơn hàng nào',
                'description' => 'Bắt đầu mua sắm để tạo đơn hàng đầu tiên.',
                'actionUrl' => route('site.home'),
                'actionText' => 'Mua sắm ngay',
            ])
        @else
            <div class="ord-table-wrap">
                <div class="table-responsive">
                    <table class="table align-middle ord-table mb-0">
                        <thead>
                            <tr>
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
                                @php($statusLabel = $order->status?->label() ?? 'Không xác định')
                                @php($paymentLabel = $order->payment_status?->label() ?? 'Không xác định')
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
                                    <td>
                                        <strong>#{{ $order->order_code ?? $order->id }}</strong>
                                    </td>
                                    <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                    <td>{{ $order->items->count() }}</td>
                                    <td><strong>{{ number_format((float) $order->total_amount, 0, ',', '.') }} đ</strong></td>
                                    <td><span class="site-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                    <td><span class="site-badge {{ $paymentClass }}">{{ $paymentLabel }}</span></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1 justify-content-end">
                                            <a href="{{ route('site.orders.show', $order->id) }}" class="btn btn-sm btn-outline-dark">Chi tiết</a>
                                            @if (
                                                $order->payment_method === \App\Enums\PaymentMethod::VNPAY
                                                && $order->payment_status === \App\Enums\PaymentStatus::FAILED
                                                && $order->status !== \App\Enums\OrderStatus::CANCELLED
                                            )
                                                <form action="{{ route('site.orders.vnpay.retry', $order->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-primary">Thanh toán lại</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">{{ $orders->links() }}</div>
        @endif
    </section>
@endsection
