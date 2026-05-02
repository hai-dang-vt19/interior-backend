@extends('base')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Đơn hàng</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.order.index') }}" method="GET" id="searchFormOrder" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Mã đơn</label>
                <input type="text" class="form-control" name="order_code" value="{{ request('order_code') }}" placeholder="VD: ORD1735..." autocomplete="off">
            </div>
            <div class="col-md-3">
                <label class="form-label">Khách hàng</label>
                <select class="form-select" name="customer_id">
                    <option value="">Tất cả</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Trạng thái đơn</label>
                <select class="form-select" name="status">
                    <option value="">Tất cả</option>
                    @foreach (App\Enums\OrderStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Thanh toán</label>
                <select class="form-select" name="payment_status">
                    <option value="">Tất cả</option>
                    @foreach (App\Enums\PaymentStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ request('payment_status') == $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Bản ghi</label>
                <select class="form-select" name="deleted">
                    <option value="active" {{ request('deleted', 'active') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="trashed" {{ request('deleted') === 'trashed' ? 'selected' : '' }}>Thùng rác</option>
                    <option value="all" {{ request('deleted') === 'all' ? 'selected' : '' }}>Tất cả</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Ngày tạo</label>
                <input type="text" class="form-control flatpickr-range" name="dateFrom" value="{{ request('dateFrom') }}" placeholder="Chọn ngày...">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary me-2">Tìm kiếm</button>
                <button type="button" class="btn btn-secondary reset-form">Đặt lại</button>
                <input type="hidden" name="per_page" id="per_page" value="{{ request('per_page') }}">
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách đơn hàng</h5>
        <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#modalCreateOrder">
            <i class="fas fa-plus"></i> Tạo đơn hàng
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="text-center">Mã đơn</th>
                        <th>Khách hàng</th>
                        <th class="text-center">Sản phẩm</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái đơn</th>
                        <th>PT thanh toán</th>
                        <th>TT thanh toán</th>
                        <th>Ngày tạo</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="text-center"><code class="small">{{ $order->order_code ?? $order->id }}</code></td>
                            <td>{{ $order->customer?->full_name }}</td>
                            <td class="text-center">{{ $order->items->count() }}</td>
                            <td>{{ $order->getTotalDisplay() }}</td>
                            <td>{{ $order->status?->label() }}</td>
                            <td>{{ $order->payment_method?->label() }}</td>
                            <td>{{ $order->payment_status?->label() }}</td>
                            <td>{{ $order->formatCreatedAt() }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('admin.order.show', $order->id) }}" class="btn btn-info btn-sm" title="Chi tiết">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house-add" viewBox="0 0 16 16">
                                            <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h4a.5.5 0 1 0 0-1h-4a.5.5 0 0 1-.5-.5V7.207l5-5 6.646 6.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293z"/>
                                            <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-3.5-2a.5.5 0 0 0-.5.5v1h-1a.5.5 0 0 0 0 1h1v1a.5.5 0 1 0 1 0v-1h1a.5.5 0 1 0 0-1h-1v-1a.5.5 0 0 0-.5-.5"/>
                                        </svg>
                                    </a>
                                    <button class="btn btn-sm btn-edit btn-edit-order"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditOrder"
                                        data-route="{{ route('admin.order.update', $order->id) }}"
                                        data-customer-id="{{ $order->customer_id }}"
                                        data-items='@json($order->items->map(fn($item) => ["product_id" => $item->product_id, "quantity" => $item->quantity])->values())'
                                        data-shipping-address="{{ $order->shipping_address }}"
                                        data-shipping-phone="{{ $order->shipping_phone }}"
                                        data-shipping-provider="{{ $order->shipping_provider }}"
                                        data-tracking-number="{{ $order->tracking_number }}"
                                        data-status="{{ $order->status?->value }}"
                                        data-payment-method="{{ $order->payment_method?->value }}"
                                        data-payment-status="{{ $order->payment_status?->value }}"
                                        data-notes="{{ $order->notes }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="m7 17.013 4.413-.015 9.632-9.54c.378-.378.586-.88.586-1.414s-.208-1.036-.586-1.414l-1.586-1.586c-.756-.756-2.075-.752-2.825-.003L7 12.583v4.43zM18.045 4.458l1.589 1.583-1.597 1.582-1.586-1.585 1.594-1.58zM9 13.417l6.03-5.973 1.586 1.586-6.029 5.971L9 15.006v-1.589z"></path><path d="M5 21h14c1.103 0 2-.897 2-2v-8.668l-2 2V19H8.158c-.026 0-.053.01-.079.01-.033 0-.066-.009-.1-.01H5V5h6.847l2-2H5c-1.103 0-2 .897-2 2v14c0 1.103.897 2 2 2z"></path></svg>
                                    </button>
                                    @if (!$order->deleted_at)
                                        <form action="{{ route('admin.order.destroy', $order->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm btn-delete-order" type="button">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.order.restore', $order->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-success btn-sm btn-restore-order" type="button">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-recycle" viewBox="0 0 24 24">
                                                    <path d="M9.302 1.256a1.5 1.5 0 0 0-2.604 0l-1.704 2.98a.5.5 0 0 0 .869.497l1.703-2.981a.5.5 0 0 1 .868 0l2.54 4.444-1.256-.337a.5.5 0 1 0-.26.966l2.415.647a.5.5 0 0 0 .613-.353l.647-2.415a.5.5 0 1 0-.966-.259l-.333 1.242zM2.973 7.773l-1.255.337a.5.5 0 1 1-.26-.966l2.416-.647a.5.5 0 0 1 .612.353l.647 2.415a.5.5 0 0 1-.966.259l-.333-1.242-2.545 4.454a.5.5 0 0 0 .434.748H5a.5.5 0 0 1 0 1H1.723A1.5 1.5 0 0 1 .421 12.24zm10.89 1.463a.5.5 0 1 0-.868.496l1.716 3.004a.5.5 0 0 1-.434.748h-5.57l.647-.646a.5.5 0 1 0-.708-.707l-1.5 1.5a.5.5 0 0 0 0 .707l1.5 1.5a.5.5 0 1 0 .708-.707l-.647-.647h5.57a1.5 1.5 0 0 0 1.302-2.244z"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.order.force-destroy', $order->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-dark btn-sm btn-force-delete-order" type="button">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-trash" viewBox="0 0 24 24">
                                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center">Không có dữ liệu</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $orders->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>

@include('order.modal.create')
@include('order.modal.edit')
@endsection

@section('scripts')
<script type="module">
$(document).ready(function() {
    const productOptions = `@foreach ($products as $product)<option value="{{ $product->id }}">{{ $product->name }} ({{ number_format((float)($product->discount_price ?? $product->price), 0, ',', '.') }} đ)</option>@endforeach`;

    const buildItemRow = (prefix, index, productId = '', quantity = 1) => {
        return `
            <div class="row g-2 order-item-row mb-2">
                <div class="col-md-8">
                    <select class="form-select" name="${prefix}[${index}][product_id]">
                        ${productOptions}
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control" name="${prefix}[${index}][quantity]" min="1" value="${quantity}">
                </div>
                <div class="col-md-1 d-grid">
                    <button type="button" class="btn btn-outline-danger btn-remove-item">&times;</button>
                </div>
            </div>
        `;
    };

    const resetCreateItems = () => {
        const wrap = $('#modalCreateOrder .order-items-create');
        wrap.html(buildItemRow('order_items', 0));
        wrap.find('select[name="order_items[0][product_id]"] option:first').prop('selected', true);
    };

    resetCreateItems();

    $('.btn-submit-create-order').on('click', function() {
        $('#modalCreateOrder form').submit();
    });

    $('.btn-add-order-item-create').on('click', function() {
        const wrap = $('#modalCreateOrder .order-items-create');
        const index = wrap.find('.order-item-row').length;
        wrap.append(buildItemRow('order_items', index));
        wrap.find(`select[name="order_items[${index}][product_id]"] option:first`).prop('selected', true);
    });

    $('#modalCreateOrder').on('click', '.btn-remove-item', function() {
        const rows = $('#modalCreateOrder .order-item-row');
        if (rows.length > 1) {
            $(this).closest('.order-item-row').remove();
        }
    });

    $('.btn-edit-order').on('click', function() {
        let target = '#modalEditOrder form';
        $(target).attr('action', $(this).data('route'));
        $(`${target} select[name=customer_id]`).val($(this).data('customer-id')).trigger('change');
        $(`${target} input[name=shipping_phone]`).val($(this).data('shipping-phone'));
        $(`${target} input[name=shipping_provider]`).val($(this).data('shipping-provider'));
        $(`${target} input[name=tracking_number]`).val($(this).data('tracking-number'));
        $(`${target} textarea[name=shipping_address]`).val($(this).data('shipping-address'));
        $(`${target} select[name=status]`).val($(this).data('status')).trigger('change');
        $(`${target} select[name=payment_method]`).val($(this).data('payment-method')).trigger('change');
        $(`${target} select[name=payment_status]`).val($(this).data('payment-status')).trigger('change');
        $(`${target} textarea[name=notes]`).val($(this).data('notes'));

        const wrap = $('#modalEditOrder .order-items-edit');
        const items = $(this).data('items') || [];
        wrap.html('');

        if (!items.length) {
            wrap.append(buildItemRow('order_items', 0));
            wrap.find('select[name="order_items[0][product_id]"] option:first').prop('selected', true);
            return;
        }

        items.forEach((item, index) => {
            wrap.append(buildItemRow('order_items', index, item.product_id, item.quantity));
            wrap.find(`select[name="order_items[${index}][product_id]"]`).val(String(item.product_id));
        });
    });

    $('.btn-add-order-item-edit').on('click', function() {
        const wrap = $('#modalEditOrder .order-items-edit');
        const index = wrap.find('.order-item-row').length;
        wrap.append(buildItemRow('order_items', index));
        wrap.find(`select[name="order_items[${index}][product_id]"] option:first`).prop('selected', true);
    });

    $('#modalEditOrder').on('click', '.btn-remove-item', function() {
        const rows = $('#modalEditOrder .order-item-row');
        if (rows.length > 1) {
            $(this).closest('.order-item-row').remove();
        }
    });

    $('.btn-submit-edit-order').on('click', function() {
        $('#modalEditOrder form').submit();
    });

    $('.btn-delete-order, .btn-restore-order, .btn-force-delete-order').on('click', function() {
        $(this).closest('form').submit();
    });
});
</script>
@endsection
