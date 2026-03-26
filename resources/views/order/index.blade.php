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
                        <th class="text-center">ID</th>
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
                            <td class="text-center">{{ $order->id }}</td>
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
                                        <i class="fas fa-eye"></i>
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
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    @if (!$order->deleted_at)
                                        <form action="{{ route('admin.order.destroy', $order->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm btn-delete-order" type="button">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.order.restore', $order->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-success btn-sm btn-restore-order" type="button">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.order.force-destroy', $order->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-dark btn-sm btn-force-delete-order" type="button">
                                                <i class="fas fa-trash-alt"></i>
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
