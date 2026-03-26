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
        <form action="{{ route('admin.customer.index') }}" method="GET" id="searchForm" class="d-flex flex-column gap-3" autocomplete="off">
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
                <div class="col-md-3">
                    <label for="loyalty_tier" class="form-label">Hạng khách hàng</label>
                    <select class="form-select" id="loyalty_tier" name="loyalty_tier">
                        <option value="">Tất cả</option>
                        <option value="standard" {{ request('loyalty_tier') === 'standard' ? 'selected' : '' }}>Standard</option>
                        <option value="silver" {{ request('loyalty_tier') === 'silver' ? 'selected' : '' }}>Silver</option>
                        <option value="gold" {{ request('loyalty_tier') === 'gold' ? 'selected' : '' }}>Gold</option>
                        <option value="platinum" {{ request('loyalty_tier') === 'platinum' ? 'selected' : '' }}>Platinum</option>
                    </select>
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
                    <label for="deleted" class="form-label">Bản ghi</label>
                    <select class="form-select" id="deleted" name="deleted">
                        <option value="active" {{ request('deleted', 'active') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="trashed" {{ request('deleted') === 'trashed' ? 'selected' : '' }}>Thùng rác</option>
                        <option value="all" {{ request('deleted') === 'all' ? 'selected' : '' }}>Tất cả</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="dateFrom" class="form-label">Ngày tạo</label>
                    <input type="text" class="form-control flatpickr-range" id="dateFrom" name="dateFrom" value="{{ request('dateFrom') }}" placeholder="Chọn ngày...">
                </div>
                <div class="col-auto d-flex align-items-end justify-content-md-start justify-content-center">
                    <button type="submit" class="btn btn-primary me-2">Tìm kiếm</button>
                    <button type="button" class="btn btn-secondary reset-form">Đặt lại</button>
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
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCreate" type="button">
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
                        <th>Hạng KH</th>
                        <th>Điểm</th>
                        <th>Ưu đãi</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $key => $customer)
                        <tr
                            @if ($customer->deleted_at)
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                data-bs-custom-class="custom-tooltip"
                                data-bs-title="Ngừng hoạt động: {{ $customer->deleted_at->format('H:i d/m/Y') }}"
                            @endif
                        >
                            <td class="text-center">{{ ($customers->currentPage() - 1) * $customers->perPage() + 1 + $key }}</td>
                            <td class="text-center">{{ $customer->id }}</td>
                            <td>{{ $customer->full_name }}</td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->phone }}</td>
                            <td>{{ $customer->formatLoyaltyTier() }}</td>
                            <td>{{ number_format((int) $customer->reward_points) }}</td>
                            <td>{{ $customer->getLoyaltyBenefit() }}</td>
                            <td>{!! $customer->formatStatus() !!}</td>
                            <td>{{ $customer->formatCreatedAt() }}</td>
                            <td>
                                <div class="d-flex flex-warp justify-content-center">
                                    <div>
                                        <a href="{{ route('admin.customer.profile', $customer->id) }}" class="btn btn-info btn-sm me-1" title="Địa chỉ & liên hệ">
                                            <i class="fas fa-address-book"></i>
                                        </a>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-edit"
                                            data-bs-toggle="modal" data-bs-target="#modalEdit"
                                            data-route="{{ route('admin.customer.update', $customer->id) }}"
                                            data-full-name="{{ $customer->full_name }}"
                                            data-email="{{ $customer->email }}"
                                            data-phone="{{ $customer->phone }}"
                                            data-loyalty-tier="{{ $customer->loyalty_tier }}"
                                            data-reward-points="{{ $customer->reward_points }}"
                                            data-status="{{ $customer->getIDStatus() }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="m7 17.013 4.413-.015 9.632-9.54c.378-.378.586-.88.586-1.414s-.208-1.036-.586-1.414l-1.586-1.586c-.756-.756-2.075-.752-2.825-.003L7 12.583v4.43zM18.045 4.458l1.589 1.583-1.597 1.582-1.586-1.585 1.594-1.58zM9 13.417l6.03-5.973 1.586 1.586-6.029 5.971L9 15.006v-1.589z"></path><path d="M5 21h14c1.103 0 2-.897 2-2v-8.668l-2 2V19H8.158c-.026 0-.053.01-.079.01-.033 0-.066-.009-.1-.01H5V5h6.847l2-2H5c-1.103 0-2 .897-2 2v14c0 1.103.897 2 2 2z"></path></svg>
                                        </button>
                                    </div>
                                    @if (!$customer->deleted_at)
                                        <form action="{{ route('admin.customer.destroy', $customer->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm btn-delete" type="button">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.customer.restore', $customer->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-success btn-sm btn-restore-customer" type="button" title="Khôi phục">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.customer.force-destroy', $customer->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-dark btn-sm btn-force-delete-customer" type="button" title="Xóa vĩnh viễn">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div id="focus_page_loading">
            {{ $customers->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>

@include('customer.modal.create')
@include('customer.modal.edit')
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
        $('.btn-edit').on('click', function() {
            let status = $(this).data('status');
            let targetModal = '#modalEdit form';

            $(targetModal).attr('action', $(this).data('route'));
            $(`${targetModal} input[name=full_name]`).val($(this).data('full-name'));
            $(`${targetModal} input[name=email]`).val($(this).data('email'));
            $(`${targetModal} input[name=phone]`).val($(this).data('phone'));
            $(`${targetModal} select[name=loyalty_tier]`).val($(this).data('loyalty-tier')).trigger('change');
            $(`${targetModal} input[name=reward_points]`).val($(this).data('reward-points'));

            if (status == 1) {
                $(`${targetModal} .select-select`).hide();
            } else {
                if ($(`${targetModal} select[name=status] option[value="` + status + '"]').length) {
                    $(`${targetModal} select[name=status]`).val(status).trigger('change');
                }
            }
        });

        $('.btn-submit-edit').on('click', function() {
            $('#modalEdit form').submit();
        });

        $('.btn-submit-create').on('click', function() {
            $('#modalCreate form').submit();
        });

        $('.btn-restore-customer').on('click', function() {
            $(this).closest('form').submit();
        });

        $('.btn-force-delete-customer').on('click', function() {
            Alert.confirm({
                title: 'Xóa vĩnh viễn khách hàng',
                text: 'Thao tác này không thể hoàn tác. Tiếp tục?',
                confirmButtonText: 'Xóa vĩnh viễn',
                denyButtonText: 'Hủy',
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).closest('form').submit();
                }
            });
        });
    });
</script>
@endsection
