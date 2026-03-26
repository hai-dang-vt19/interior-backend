@extends('base')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Danh mục</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.category.index') }}" method="GET" id="searchFormCategory" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tên danh mục</label>
                <input type="text" class="form-control" name="name" value="{{ request('name') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Bản ghi</label>
                <select class="form-select" name="deleted">
                    <option value="active" {{ request('deleted', 'active') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="trashed" {{ request('deleted') === 'trashed' ? 'selected' : '' }}>Thùng rác</option>
                    <option value="all" {{ request('deleted') === 'all' ? 'selected' : '' }}>Tất cả</option>
                </select>
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
        <h5 class="mb-0">Danh sách danh mục</h5>
        <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#modalCreateCategory">
            <i class="fas fa-plus"></i> Thêm mới
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="text-center">ID</th>
                        <th>Tên</th>
                        <th>Danh mục cha</th>
                        <th>Mô tả</th>
                        <th>Ngày tạo</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="text-center">{{ $category->id }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->parent?->name }}</td>
                            <td>{{ $category->description }}</td>
                            <td>{{ $category->formatCreatedAt() }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-edit btn-edit-category"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditCategory"
                                        data-route="{{ route('admin.category.update', $category->id) }}"
                                        data-id="{{ $category->id }}"
                                        data-name="{{ $category->name }}"
                                        data-parent-id="{{ $category->parent_id }}"
                                        data-description="{{ $category->description }}">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    @if (!$category->deleted_at)
                                        <form action="{{ route('admin.category.destroy', $category->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm btn-delete-category" type="button">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.category.restore', $category->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-success btn-sm btn-restore-category" type="button">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.category.force-destroy', $category->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-dark btn-sm btn-force-delete-category" type="button">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Không có dữ liệu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $categories->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>

@include('category.modal.create')
@include('category.modal.edit')
@endsection

@section('scripts')
<script type="module">
$(document).ready(function() {
    $('.btn-submit-create-category').on('click', function() {
        $('#modalCreateCategory form').submit();
    });

    $('.btn-edit-category').on('click', function() {
        let target = '#modalEditCategory form';
        $(target).attr('action', $(this).data('route'));
        $(`${target} input[name=name]`).val($(this).data('name'));
        $(`${target} textarea[name=description]`).val($(this).data('description'));

        let currentId = String($(this).data('id'));
        $(`${target} select[name=parent_id] option`).prop('disabled', false);
        $(`${target} select[name=parent_id] option[value="${currentId}"]`).prop('disabled', true);
        $(`${target} select[name=parent_id]`).val($(this).data('parent-id') ?? '').trigger('change');
    });

    $('.btn-submit-edit-category').on('click', function() {
        $('#modalEditCategory form').submit();
    });

    $('.btn-delete-category, .btn-restore-category, .btn-force-delete-category').on('click', function() {
        $(this).closest('form').submit();
    });
});
</script>
@endsection
