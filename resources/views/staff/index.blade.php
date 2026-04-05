@extends('base')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Nhân viên</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.staff.index') }}" method="GET" class="row g-3" id="searchFormStaff">
            <div class="col-md-4">
                <label class="form-label">Từ khóa</label>
                <input type="text" class="form-control" name="keyword" value="{{ request('keyword') }}" placeholder="Tên, username, email, phone">
            </div>
            <div class="col-md-4">
                <label class="form-label">Bản ghi</label>
                <select class="form-select" name="deleted">
                    <option value="active" {{ request('deleted', 'active') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="trashed" {{ request('deleted') === 'trashed' ? 'selected' : '' }}>Đã vô hiệu</option>
                    <option value="all" {{ request('deleted') === 'all' ? 'selected' : '' }}>Tất cả</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary me-2">Tìm kiếm</button>
                <a class="btn btn-secondary" href="{{ route('admin.staff.index') }}">Đặt lại</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách nhân viên</h5>
        <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#modalCreateStaff">
            <i class="fas fa-plus"></i> Thêm mới
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="text-center">ID</th>
                        <th>Username</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffs as $staff)
                        <tr>
                            <td class="text-center">{{ $staff->id }}</td>
                            <td>{{ $staff->username }}</td>
                            <td>{{ $staff->full_name }}</td>
                            <td>{{ $staff->email }}</td>
                            <td>{{ $staff->phone }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-edit btn-edit-staff"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditStaff"
                                        data-route="{{ route('admin.staff.update', $staff->id) }}"
                                        data-username="{{ $staff->username }}"
                                        data-full-name="{{ $staff->full_name }}"
                                        data-email="{{ $staff->email }}"
                                        data-phone="{{ $staff->phone }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="m7 17.013 4.413-.015 9.632-9.54c.378-.378.586-.88.586-1.414s-.208-1.036-.586-1.414l-1.586-1.586c-.756-.756-2.075-.752-2.825-.003L7 12.583v4.43zM18.045 4.458l1.589 1.583-1.597 1.582-1.586-1.585 1.594-1.58zM9 13.417l6.03-5.973 1.586 1.586-6.029 5.971L9 15.006v-1.589z"></path><path d="M5 21h14c1.103 0 2-.897 2-2v-8.668l-2 2V19H8.158c-.026 0-.053.01-.079.01-.033 0-.066-.009-.1-.01H5V5h6.847l2-2H5c-1.103 0-2 .897-2 2v14c0 1.103.897 2 2 2z"></path></svg>
                                    </button>
                                    @if (!$staff->deleted_at)
                                        <form action="{{ route('admin.staff.destroy', $staff->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm btn-delete-staff" type="button">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.staff.restore', $staff->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-success btn-sm btn-restore-staff" type="button">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-recycle" viewBox="0 0 24 24">
                                                    <path d="M9.302 1.256a1.5 1.5 0 0 0-2.604 0l-1.704 2.98a.5.5 0 0 0 .869.497l1.703-2.981a.5.5 0 0 1 .868 0l2.54 4.444-1.256-.337a.5.5 0 1 0-.26.966l2.415.647a.5.5 0 0 0 .613-.353l.647-2.415a.5.5 0 1 0-.966-.259l-.333 1.242zM2.973 7.773l-1.255.337a.5.5 0 1 1-.26-.966l2.416-.647a.5.5 0 0 1 .612.353l.647 2.415a.5.5 0 0 1-.966.259l-.333-1.242-2.545 4.454a.5.5 0 0 0 .434.748H5a.5.5 0 0 1 0 1H1.723A1.5 1.5 0 0 1 .421 12.24zm10.89 1.463a.5.5 0 1 0-.868.496l1.716 3.004a.5.5 0 0 1-.434.748h-5.57l.647-.646a.5.5 0 1 0-.708-.707l-1.5 1.5a.5.5 0 0 0 0 .707l1.5 1.5a.5.5 0 1 0 .708-.707l-.647-.647h5.57a1.5 1.5 0 0 0 1.302-2.244z"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.staff.force-destroy', $staff->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-dark btn-sm btn-force-delete-staff" type="button">
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
                        <tr><td colspan="6" class="text-center">Không có dữ liệu</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $staffs->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>

@include('staff.modal.create')
@include('staff.modal.edit')
@endsection

@section('scripts')
<script type="module">
$(document).ready(function() {
    $('.btn-submit-create-staff').on('click', function() {
        $('#modalCreateStaff form').submit();
    });

    $('.btn-edit-staff').on('click', function() {
        const target = '#modalEditStaff form';
        $(target).attr('action', $(this).data('route'));
        $(`${target} input[name=username]`).val($(this).data('username'));
        $(`${target} input[name=full_name]`).val($(this).data('full-name'));
        $(`${target} input[name=email]`).val($(this).data('email'));
        $(`${target} input[name=phone]`).val($(this).data('phone'));
        $(`${target} input[name=password]`).val('');
    });

    $('.btn-submit-edit-staff').on('click', function() {
        $('#modalEditStaff form').submit();
    });

    $('.btn-delete-staff, .btn-restore-staff, .btn-force-delete-staff').on('click', function() {
        $(this).closest('form').submit();
    });
});
</script>
@endsection
