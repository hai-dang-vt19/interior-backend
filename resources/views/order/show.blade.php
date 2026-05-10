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
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">Thông tin đơn hàng</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Khách hàng:</strong> {{ $order->customer?->full_name }}</p>
                    <p class="mb-1"><strong>SĐT giao hàng:</strong> {{ $order->shipping_phone }}</p>
                    <p class="mb-1"><strong>Địa chỉ:</strong> {{ $order->shipping_address }}</p>
                    <p class="mb-1"><strong>Đơn vị VC:</strong> {{ $order->shipping_provider ?? '-' }}</p>
                    <p class="mb-1"><strong>Mã vận đơn:</strong> {{ $order->tracking_number ?? '-' }}</p>
                    <p class="mb-1"><strong>Ngày gửi:</strong> {{ $order->shipped_at?->format('d/m/Y H:i') ?? '-' }}</p>
                    <p class="mb-1"><strong>Ngày giao:</strong> {{ $order->delivered_at?->format('d/m/Y H:i') ?? '-' }}
                    </p>
                    <p class="mb-1"><strong>Trạng thái:</strong> {{ $order->status?->label() }}</p>
                    <p class="mb-1"><strong>Thanh toán:</strong> {{ $order->payment_method?->label() }} /
                        {{ $order->payment_status?->label() }}</p>
                    <p class="mb-0"><strong>Tổng đơn (total_amount):</strong> {{ $order->getTotalDisplay() }}</p>
                    @if ($order->loyaltyTierSnapshotLabel())
                        <p class="mb-1 small text-muted">
                            <strong>Hạng áp dụng (lúc lưu đơn):</strong> {{ $order->loyaltyTierSnapshotLabel() }}
                            @php($pctSnap = $order->loyaltyTierPercentSnapshot())
                            @if ($pctSnap !== null && $pctSnap > 0)
                                — chiết khấu {{ $pctSnap }}% trên tạm tính
                            @endif
                        </p>
                    @endif
                    @if ((int) $order->loyalty_discount_amount > 0)
                        <p class="mb-0 small text-muted">Đã trừ chiết khấu hạng: −{{ number_format((int) $order->loyalty_discount_amount, 0, ',', '.') }} đ</p>
                    @elseif ($order->loyaltyTierSnapshotLabel())
                        <p class="mb-0 small text-muted">Không có chiết khấu % (hạng Standard hoặc tạm tính 0).</p>
                    @endif
                    @if ($order->notes)
                        <p class="mb-0 mt-2"><strong>Ghi chú khách:</strong> {{ $order->notes }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Tạo yêu cầu đổi/trả</h6>
                    <a href="{{ route('admin.order.invoice', $order->id) }}" class="btn btn-sm btn-success">Xuất hóa đơn
                        PDF</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.order.shipping.update', $order->id) }}" method="POST"
                        class="row g-2 mb-3">
                        @csrf
                        @method('PATCH')
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="shipping_provider"
                                value="{{ $order->shipping_provider }}" placeholder="Đơn vị vận chuyển">
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="tracking_number"
                                value="{{ $order->tracking_number }}" placeholder="Mã vận đơn">
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" name="status">
                                @foreach (App\Enums\OrderStatus::cases() as $status)
                                    <option value="{{ $status->value }}"
                                        {{ $order->status?->value === $status->value ? 'selected' : '' }}>
                                        {{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="datetime-local" class="form-control" name="shipped_at"
                                value="{{ $order->shipped_at?->format('Y-m-d\TH:i') }}">
                        </div>
                        <div class="col-md-6">
                            <input type="datetime-local" class="form-control" name="delivered_at"
                                value="{{ $order->delivered_at?->format('Y-m-d\TH:i') }}">
                        </div>
                        <div class="col-12">
                            <input type="text" class="form-control" name="note"
                                placeholder="Ghi chú cập nhật giao hàng">
                        </div>
                        <div class="col-12 d-grid">
                            <button type="submit" class="btn btn-warning">Cập nhật giao hàng</button>
                        </div>
                    </form>

                    <form action="{{ route('admin.order.return.store', $order->id) }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-md-4">
                            <select class="form-select" name="type">
                                <option value="return">Trả hàng</option>
                                <option value="exchange">Đổi hàng</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="reason" placeholder="Lý do">
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
