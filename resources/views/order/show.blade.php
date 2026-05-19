@extends('base')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.order.index') }}">Đơn hàng</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chi tiết đơn {{ $order->order_code ?? '#'.$order->id }}</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-lg-6">
            @php
                use App\Enums\OrderStatus;
                use App\Enums\PaymentStatus;
                $orderStatusBadge =
                    match ($order->status) {
                        OrderStatus::PENDING => 'text-bg-warning',
                        OrderStatus::CONFIRMED => 'text-bg-info',
                        OrderStatus::SHIPPING => 'text-bg-primary',
                        OrderStatus::DELIVERED => 'text-bg-success',
                        OrderStatus::CANCELLED => 'text-bg-secondary',
                        default => 'text-bg-light text-dark border',
                    };
                $paymentStatusBadge =
                    match ($order->payment_status) {
                        PaymentStatus::PAID => 'text-bg-success',
                        PaymentStatus::FAILED => 'text-bg-danger',
                        PaymentStatus::PENDING => 'text-bg-warning text-dark',
                        default => 'text-bg-light text-dark border',
                    };
            @endphp
            <div class="card h-100 border shadow-sm">
                <div class="card-header bg-body-secondary border-bottom py-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                        <div>
                            <h6 class="mb-1">Thông tin đơn hàng</h6>
                            <div class="text-muted small">
                                Đặt lúc <strong>{{ $order->created_at?->format('d/m/Y H:i') ?? '—' }}</strong>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-1 justify-content-end">
                            <span class="badge rounded-pill {{ $orderStatusBadge }}">{{ $order->status?->label() }}</span>
                            <span class="badge rounded-pill {{ $paymentStatusBadge }}">{{ $order->payment_status?->label() }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="rounded-3 bg-body-secondary px-3 py-2 mb-4">
                        <div class="small text-muted mb-0">Mã đơn</div>
                        <code class="d-block fs-5 fw-semibold text-body">{{ $order->order_code ?: '#'.$order->id }}</code>
                    </div>

                    @php($sepRow = 'row g-2 small align-items-start border-bottom border-light pb-3 mb-3 mx-0')
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-muted fw-bold small mb-3 pb-2 border-bottom border-light">Khách &amp;
                                nhận hàng</h6>
                            <div class="mb-0">
                                <div class="{{ $sepRow }}">
                                    <div class="col-5 text-muted">Khách hàng</div>
                                    <div class="col-7 fw-medium text-break">{{ $order->customer?->full_name ?? '—' }}</div>
                                </div>
                                <div class="{{ $sepRow }}">
                                    <div class="col-5 text-muted">SĐT giao hàng</div>
                                    <div class="col-7">{{ $order->shipping_phone }}</div>
                                </div>
                                <div class="row g-2 small mx-0">
                                    <div class="col-12 text-muted">Địa chỉ giao</div>
                                    <div class="col-12 text-break">{{ $order->shipping_address }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-muted fw-bold small mb-3 pb-2 border-bottom border-light">Vận chuyển
                            </h6>
                            <div class="mb-0">
                                <div class="{{ $sepRow }}">
                                    <div class="col-5 text-muted">Đơn vị VC</div>
                                    <div class="col-7">{{ $order->shipping_provider ?? '—' }}</div>
                                </div>
                                <div class="{{ $sepRow }}">
                                    <div class="col-5 text-muted">Mã vận đơn</div>
                                    <div class="col-7">{{ $order->tracking_number ?? '—' }}</div>
                                </div>
                                <div class="{{ $sepRow }} border-0 mb-2 pb-0">
                                    <div class="col-5 text-muted">Ngày gửi</div>
                                    <div class="col-7">{{ $order->shipped_at?->format('d/m/Y H:i') ?? '—' }}</div>
                                </div>
                                <div class="row g-2 small mx-0">
                                    <div class="col-5 text-muted">Ngày giao</div>
                                    <div class="col-7">{{ $order->delivered_at?->format('d/m/Y H:i') ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted fw-bold small mb-3 pb-2 border-bottom border-light">Thanh toán
                            </h6>
                            <div class="mb-0">
                                <div class="{{ $sepRow }}">
                                    <div class="col-sm-4 col-lg-3 text-muted">Phương thức</div>
                                    <div class="col-sm-8 col-lg-9">{{ $order->payment_method?->label() ?? '—' }}</div>
                                </div>
                                <div class="{{ $sepRow }}">
                                    <div class="col-sm-4 col-lg-3 text-muted">Trạng thái TT</div>
                                    <div class="col-sm-8 col-lg-9">{{ $order->payment_status?->label() ?? '—' }}</div>
                                </div>
                                <div class="row g-2 align-items-baseline mx-0">
                                    <div class="col-sm-4 col-lg-3 text-muted small">Tổng thanh toán</div>
                                    <div class="col-sm-8 col-lg-9 fs-5 fw-semibold text-success">{{ $order->getTotalDisplay() }}
                                    </div>
                                </div>
                                @if ($order->loyaltyTierSnapshotLabel())
                                    <div class="small text-muted border-top pt-3 mt-3">
                                        Hạng lúc lưu đơn: <strong>{{ $order->loyaltyTierSnapshotLabel() }}</strong>
                                        @php($pctSnap = $order->loyaltyTierPercentSnapshot())
                                        @if ($pctSnap !== null && $pctSnap > 0)
                                            · Chiết khấu {{ $pctSnap }}% trên tạm tính
                                        @endif
                                    </div>
                                @endif
                                @if ((int) $order->loyalty_discount_amount > 0)
                                    <div class="small text-success mt-2">Đã trừ chiết khấu hạng: −{{ number_format((int) $order->loyalty_discount_amount, 0, ',', '.') }}
                                        đ</div>
                                @elseif ($order->loyaltyTierSnapshotLabel())
                                    <div class="small text-muted mt-2">Không có chiết khấu % hạng (Standard hoặc tạm tính 0).</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($order->notes)
                        <div class="alert alert-secondary border mt-4 py-2 px-3 small mb-0">
                            <strong class="d-block text-dark mb-1">Ghi chú khách</strong>
                            <span class="text-break">{{ $order->notes }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6 d-flex flex-column gap-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="mb-0">Cập nhật giao hàng</h6>
                    <a href="{{ route('admin.order.invoice', $order->id) }}" class="btn btn-success">Xuất hóa đơn
                        PDF</a>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">Đơn vị VC, vận đơn, trạng thái đơn và mốc thời gian gửi/giao.</p>
                    <form action="{{ route('admin.order.shipping.update', $order->id) }}" method="POST" class="row g-2">
                        @csrf
                        @method('PATCH')
                        <div class="col-md-4">
                            <label class="form-label small mb-0">Đơn vị vận chuyển</label>
                            <input type="text" class="form-control" name="shipping_provider"
                                value="{{ $order->shipping_provider }}" placeholder="Đơn vị vận chuyển">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-0">Mã vận đơn</label>
                            <input type="text" class="form-control" name="tracking_number"
                                value="{{ $order->tracking_number }}" placeholder="Mã vận đơn">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-0">Trạng thái đơn @include('component.required-mark')</label>
                            <select class="form-select" name="status">
                                @foreach (App\Enums\OrderStatus::cases() as $status)
                                    <option value="{{ $status->value }}"
                                        {{ $order->status?->value === $status->value ? 'selected' : '' }}>
                                        {{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-0">Ngày gửi</label>
                            <input type="datetime-local" class="form-control" name="shipped_at"
                                value="{{ $order->shipped_at?->format('Y-m-d\TH:i') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-0">Ngày giao</label>
                            <input type="datetime-local" class="form-control" name="delivered_at"
                                value="{{ $order->delivered_at?->format('Y-m-d\TH:i') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small mb-0">Ghi chú</label>
                            <input type="text" class="form-control" name="note"
                                placeholder="Ghi chú cập nhật giao hàng">
                        </div>
                        <div class="col-12 d-grid">
                            <button type="submit" class="btn btn-warning">Lưu cập nhật giao hàng</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Tạo yêu cầu đổi / trả hàng</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">Ghi nhận yêu cầu nội bộ cho đơn này; theo dõi danh sách bên dưới mục
                        “Yêu cầu đổi/trả”.</p>
                    <form action="{{ route('admin.order.return.store', $order->id) }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label small mb-0">Loại @include('component.required-mark')</label>
                            <select class="form-select" name="type">
                                <option value="return">Trả hàng</option>
                                <option value="exchange">Đổi hàng</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small mb-0">Lý do @include('component.required-mark')</label>
                            <input type="text" class="form-control" name="reason" placeholder="Lý do" required>
                        </div>
                        <div class="col-12 d-grid">
                            <button type="submit" class="btn btn-primary">Gửi yêu cầu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h6 class="mb-0">Sản phẩm trong đơn</h6>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>SL</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @php($linesSum = 0)
                    @foreach ($order->items as $item)
                        @php($lineAmount = (float) $item->price * (int) $item->quantity)
                        @php($linesSum += $lineAmount)
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
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format((float) $item->price, 0, ',', '.') }} đ</td>
                            <td>{{ number_format($lineAmount, 0, ',', '.') }} đ</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="3" class="text-end">Cộng dòng</th>
                        <th>{{ number_format($linesSum, 0, ',', '.') }} đ</th>
                    </tr>
                    @if ((int) $order->loyalty_discount_amount > 0)
                        <tr>
                            <th colspan="3" class="text-end text-muted">
                                Chiết khấu hạng
                                @if ($order->loyaltyTierSnapshotLabel())
                                    ({{ $order->loyaltyTierSnapshotLabel() }}@if (($p = $order->loyaltyTierPercentSnapshot()) !== null && $p > 0), {{ $p }}%@endif)
                                @endif
                            </th>
                            <th class="text-success">− {{ number_format((int) $order->loyalty_discount_amount, 0, ',', '.') }} đ</th>
                        </tr>
                    @endif
                    <tr>
                        <th colspan="3" class="text-end">Tổng đơn (total_amount)</th>
                        <th class="text-success">{{ $order->getTotalDisplay() }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="row g-3 mt-0">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">Lịch sử đơn hàng</h6>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Hành động</th>
                                <th>Ghi chú</th>
                                <th>Nhân viên</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->histories as $history)
                                <tr>
                                    <td>{{ $history->created_at?->format('d/m/Y H:i') }}</td>
                                    <td>{{ $history->action }}</td>
                                    <td>{{ $history->note }}</td>
                                    <td>{{ $history->changedBy?->full_name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Chưa có lịch sử</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">Yêu cầu đổi/trả</h6>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Loại</th>
                                <th>Lý do</th>
                                <th>Trạng thái</th>
                                <th>Xử lý</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->returnRequests as $req)
                                <tr>
                                    <td>{{ $req->type === 'exchange' ? 'Đổi hàng' : 'Trả hàng' }}</td>
                                    <td>{{ $req->reason }}</td>
                                    <td>{{ strtoupper($req->status) }}</td>
                                    <td>
                                        <form action="{{ route('admin.order.return.update', [$order->id, $req->id]) }}"
                                            method="POST" class="d-flex gap-1">
                                            @csrf
                                            @method('PATCH')
                                            <select class="form-select form-select-sm" name="status">
                                                @foreach (['pending', 'approved', 'rejected', 'completed'] as $status)
                                                    <option value="{{ $status }}"
                                                        {{ $req->status === $status ? 'selected' : '' }}>
                                                        {{ strtoupper($status) }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-sm btn-primary" type="submit">Lưu</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Chưa có yêu cầu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
