@extends('base')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Khách hàng</li>
        </ol>
    </nav>
@endsection

@section('content')
<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.customer.index') }}" method="GET" id="searchForm" class="d-flex flex-column gap-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="full_name" class="form-label">Tên khách hàng</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="{{ request('full_name') }}" placeholder="Nhập tên khách hàng...">
                </div>
                <div class="col-md-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control trim-space" id="email" name="email" value="{{ request('email') }}" placeholder="example@gmail.com">
                </div>
                <div class="col-md-3">
                    <label for="phone" class="form-label">Số điện thoại</label>
                    <input type="text" class="form-control input-number" id="phone" name="phone" value="{{ request('phone') }}" placeholder="Nhập số điện thoại...">
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-select" id="status" name="status">
                        <option value="{{ App\Enums\CustomerStatus::ACTIVE->value }}" {{ request('status') == App\Enums\CustomerStatus::ACTIVE->value ? 'selected' : '' }}>
                            {{ App\Enums\CustomerStatus::ACTIVE->label() }}
                        </option>
                        <option value="{{ App\Enums\CustomerStatus::INACTIVE->value }}" {{ request('status') == App\Enums\CustomerStatus::INACTIVE->value ? 'selected' : '' }}>
                            {{ App\Enums\CustomerStatus::INACTIVE->label() }}
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="dateFrom" class="form-label">Ngày tạo</label>
                    <input type="text" class="form-control flatpickr-range" id="dateFrom" name="dateFrom" placeholder="Chọn ngày...">
                </div>
                <div class="col-auto d-flex align-items-end justify-content-md-start justify-content-center">
                    <button type="submit" class="btn btn-primary me-2">Tìm kiếm</button>
                    <button type="button" class="btn btn-secondary resetForm">Đặt lại</button>
                </div>
                <input type="hidden" name="per_page" id="per_page" value="{{ request('per_page') }}">
            </div>
        </form>
    </div>
</div>

<!-- Table Section -->
<div class="card mb-3">
    <div class="card-header">
        <div class="row align-items-center justify-content-between gap-3">
            <div class="col-auto">
                <div class="d-flex flex-row flex-wrap align-items-center gap-3">
                    <h5 class="card-title mb-0">Danh sách khách hàng</h5>
                    <select id="per_page_select" data-submit-form="#searchForm" class="form-select w-25">
                        @foreach (App\Enums\PerPage::cases() as $perPage)
                            <option value="{{ $perPage->value }}" {{ request('per_page') == $perPage->value ? 'selected' : '' }}>
                                {{ $perPage->value }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class=" col-auto justify-content-end">
                <button class="btn btn-success">
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
                        <th class="text-center">ID User</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $key => $customer)
                        <tr>
                            <td class="text-center">{{ ($customers->currentPage() - 1) * $customers->perPage() + 1 + $key }}</td>
                            <td class="text-center">{{ $customer->id }}</td>
                            <td>{{ $customer->full_name }}</td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->phone }}</td>
                            <td>{!! $customer->formatStatus() !!}</td>
                            <td>{{ $customer->formatCreatedAt() }}</td>
                            <td>
                                <div class="d-flex flex-warp">
                                    <div>
                                        <button class="btn btn-sm btn-edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="m7 17.013 4.413-.015 9.632-9.54c.378-.378.586-.88.586-1.414s-.208-1.036-.586-1.414l-1.586-1.586c-.756-.756-2.075-.752-2.825-.003L7 12.583v4.43zM18.045 4.458l1.589 1.583-1.597 1.582-1.586-1.585 1.594-1.58zM9 13.417l6.03-5.973 1.586 1.586-6.029 5.971L9 15.006v-1.589z"></path><path d="M5 21h14c1.103 0 2-.897 2-2v-8.668l-2 2V19H8.158c-.026 0-.053.01-.079.01-.033 0-.066-.009-.1-.01H5V5h6.847l2-2H5c-1.103 0-2 .897-2 2v14c0 1.103.897 2 2 2z"></path></svg>
                                        </button>
                                    </div>
                                    <form action="{{ route('admin.customer.destroy', $customer->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm btn-delete" type="button">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $customers->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>
@endsection

@section('scripts')
<script type="module">
    $(document).ready(function() {
        $('.btn-delete').on('click', function() {
            Alert.confirm({
                title: 'Xóa khách hàng',
                text: 'Bạn có chắc chắn muốn xóa khách hàng này?',
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
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            // Reset error states
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                },
                error: function(xhr) {
                    Loading.hide();
                    if (xhr.status === 422) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors;
                        for (const field in errors) {
                            const input = $(`#${field}`);
                            const errorDiv = $(`#${field}-error`);
                            
                            input.addClass('is-invalid');
                            errorDiv.text(errors[field][0]);
                        }
                    } else if (xhr.status === 401 || xhr.status === 403) {
                        // Authentication errors
                        Alert.error(xhr.responseJSON.error.msg);
                    }
                }
            });
        });
    });
</script>
@endsection 