@extends('base')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Sản phẩm</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.product.index') }}" method="GET" id="searchFormProduct" class="row g-3" autocomplete="off">
            <div class="col-md-3">
                <label class="form-label">Tên sản phẩm</label>
                <input type="text" class="form-control" name="name" value="{{ request('name') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Danh mục</label>
                <select class="form-select" name="category_id">
                    <option value="">Tất cả</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Trạng thái</label>
                <select class="form-select" name="status">
                    <option value="">Tất cả</option>
                    @foreach (App\Enums\ProductStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
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
            <div class="col-12 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Tìm kiếm</button>
                <button type="button" class="btn btn-secondary reset-form">Đặt lại</button>
                <input type="hidden" name="per_page" id="per_page" value="{{ request('per_page') }}">
            </div>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <div class="row align-items-center justify-content-between gap-3">
            <div class="col-auto">
                <div class="d-flex flex-row flex-wrap align-items-center gap-3">
                    <h5 class="card-title mb-0">Danh sách sản phẩm</h5>
                    <select id="per_page_select" data-submit-form="#searchFormProduct" class="form-select w-25">
                        @foreach (App\Enums\PerPage::cases() as $perPage)
                            <option value="{{ $perPage->value }}" {{ request('per_page') == $perPage->value ? 'selected' : '' }}>
                                {{ $perPage->value }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-auto justify-content-end">
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalPickupProductSlider">
                    <i class="fas fa-plus"></i> Chọn sản phẩm banner
                </button>
                <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#modalCreateProduct">
                    <i class="fas fa-plus"></i> Thêm mới
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="text-center">STT</th>
                        <th class="text-center">ID</th>
                        <th>Tên</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $key => $product)
                        <tr
                            @if ($product->deleted_at)
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                data-bs-custom-class="custom-tooltip"
                                data-bs-title="Đã xóa mềm: {{ $product->deleted_at->format('H:i d/m/Y') }}"
                            @endif
                        >
                            <td class="text-center">{{ ($products->currentPage() - 1) * $products->perPage() + 1 + $key }}</td>
                            <td class="text-center">{{ $product->id }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category?->name }}</td>
                            <td>{{ $product->getPriceDisplay() }}</td>
                            <td>{{ $product->quantity }}</td>
                            <td>{!! $product->formatStatus() !!}</td>
                            <td>{{ $product->formatCreatedAt() }}</td>
                            <td>
                                <div class="d-flex flex-warp justify-content-center">
                                    <div>
                                        <a href="{{ route('admin.product.images', $product->id) }}" class="btn btn-info btn-sm me-1" title="Quản lý ảnh">
                                            <i class="fas fa-image"></i>
                                        </a>
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.product.inventory', $product->id) }}" class="btn btn-warning btn-sm me-1" title="Tồn kho">
                                            <i class="fas fa-boxes"></i>
                                        </a>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-edit btn-edit-product"
                                            data-bs-toggle="modal" data-bs-target="#modalEditProduct"
                                            data-route="{{ route('admin.product.update', $product->id) }}"
                                            data-name="{{ $product->name }}"
                                            data-category-id="{{ $product->category_id }}"
                                            data-description="{{ $product->description }}"
                                            data-price="{{ $product->price }}"
                                            data-discount-price="{{ $product->discount_price }}"
                                            data-quantity="{{ $product->quantity }}"
                                            data-status="{{ $product->status?->value }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="m7 17.013 4.413-.015 9.632-9.54c.378-.378.586-.88.586-1.414s-.208-1.036-.586-1.414l-1.586-1.586c-.756-.756-2.075-.752-2.825-.003L7 12.583v4.43zM18.045 4.458l1.589 1.583-1.597 1.582-1.586-1.585 1.594-1.58zM9 13.417l6.03-5.973 1.586 1.586-6.029 5.971L9 15.006v-1.589z"></path><path d="M5 21h14c1.103 0 2-.897 2-2v-8.668l-2 2V19H8.158c-.026 0-.053.01-.079.01-.033 0-.066-.009-.1-.01H5V5h6.847l2-2H5c-1.103 0-2 .897-2 2v14c0 1.103.897 2 2 2z"></path></svg>
                                        </button>
                                    </div>
                                    <form action="{{ route('admin.product.destroy', $product->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        @if (!$product->deleted_at)
                                            <button class="btn btn-danger btn-sm btn-delete btn-delete-product" type="button">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
                                            </button>
                                        @endif
                                    </form>
                                    @if ($product->deleted_at)
                                        <form action="{{ route('admin.product.restore', $product->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-success btn-sm btn-restore-product" type="button" title="Khôi phục">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.product.force-destroy', $product->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-dark btn-sm btn-force-delete-product" type="button" title="Xóa vĩnh viễn">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Không có dữ liệu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div id="focus_page_loading">
            {{ $products->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>

@include('product.modal.create')
@include('product.modal.edit')
@include('product.modal.pickup')

@endsection

@section('scripts')
<script type="module">
    $(document).ready(function() {
        $('.btn-submit-create-product').on('click', function() {
            $('#modalCreateProduct form').submit();
        });

        $('.btn-edit-product').on('click', function() {
            let targetModal = '#modalEditProduct form';
            $(targetModal).attr('action', $(this).data('route'));
            $(`${targetModal} input[name=name]`).val($(this).data('name'));
            $(`${targetModal} select[name=category_id]`).val($(this).data('category-id')).trigger('change');
            $(`${targetModal} textarea[name=description]`).val($(this).data('description'));
            $(`${targetModal} input[name=price]`).val($(this).data('price'));
            $(`${targetModal} input[name=discount_price]`).val($(this).data('discount-price'));
            $(`${targetModal} input[name=quantity]`).val($(this).data('quantity'));
            $(`${targetModal} select[name=status]`).val($(this).data('status')).trigger('change');
        });

        $('.btn-submit-edit-product').on('click', function() {
            $('#modalEditProduct form').submit();
        });

        $('.btn-delete-product').on('click', function() {
            Alert.confirm({
                title: 'Xóa sản phẩm',
                text: 'Bạn có chắc chắn muốn xóa sản phẩm này?',
                confirmButtonText: 'Xóa',
                denyButtonText: 'Hủy',
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).closest('form').submit();
                } else {
                    Alert.error('Xóa không thành công');
                }
            });
        });

        $('.btn-restore-product').on('click', function() {
            Alert.confirm({
                title: 'Khôi phục sản phẩm',
                text: 'Bạn có muốn khôi phục sản phẩm này?',
                confirmButtonText: 'Khôi phục',
                denyButtonText: 'Hủy',
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).closest('form').submit();
                }
            });
        });

        $('.btn-force-delete-product').on('click', function() {
            Alert.confirm({
                title: 'Xóa vĩnh viễn sản phẩm',
                text: 'Thao tác này không thể hoàn tác. Tiếp tục?',
                confirmButtonText: 'Xóa vĩnh viễn',
                denyButtonText: 'Hủy',
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).closest('form').submit();
                }
            });
        });

        const renderBannerPreview = function() {
            const renderColumn = function(column, previewSelector) {
                const selected = [];
                $(`.js-banner-select[data-column="${column}"]`).each(function() {
                    const value = $(this).val();
                    const label = $(this).find('option:selected').text();
                    if (value) {
                        selected.push(label);
                    }
                });

                const $preview = $(previewSelector);
                if (selected.length === 0) {
                    $preview.html('<li class="list-group-item text-muted">Chưa chọn sản phẩm</li>');
                    return;
                }

                const html = selected.map((text) => `<li class="list-group-item">${text}</li>`).join('');
                $preview.html(html);
            };

            renderColumn('left', '#bannerPreviewLeft');
            renderColumn('right', '#bannerPreviewRight');
        };

        $('.js-banner-select').on('change', function() {
            const currentValue = $(this).val();
            const column = $(this).data('column');

            if (!currentValue) {
                renderBannerPreview();
                return;
            }

            let duplicateCount = 0;
            $(`.js-banner-select[data-column="${column}"]`).each(function() {
                if ($(this).val() === currentValue) {
                    duplicateCount++;
                }
            });

            if (duplicateCount > 1) {
                Alert.error('Mỗi cột không được chọn trùng sản phẩm');
                $(this).val('');
            }

            renderBannerPreview();
        });

        renderBannerPreview();

        $('#bannerPickupForm').on('submit', function(event) {
            const leftCount = $('.js-banner-select[data-column="left"]').filter(function() { return !!$(this).val(); }).length;
            const rightCount = $('.js-banner-select[data-column="right"]').filter(function() { return !!$(this).val(); }).length;

            if (leftCount > 3 || rightCount > 3) {
                event.preventDefault();
                Alert.error('Mỗi cột chỉ được chọn tối đa 3 sản phẩm');
                return;
            }
        });
    });
</script>
@endsection
