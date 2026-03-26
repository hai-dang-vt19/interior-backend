@extends('base')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">Truy cập nhanh chức năng</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-lg-3 col-md-6">
                <div class="border rounded p-3 h-100">
                    <h6 class="mb-2">Quản lý sản phẩm</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.product.index') }}" class="btn btn-sm btn-outline-primary">Danh sách / lọc</a>
                        <a href="{{ route('admin.product.index') }}" class="btn btn-sm btn-outline-success">Thêm mới</a>
                        <a href="{{ route('admin.product.index') }}" class="btn btn-sm btn-outline-secondary">Giá / khuyến mãi</a>
                        <a href="{{ route('admin.category.index') }}" class="btn btn-sm btn-outline-dark">Danh mục</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="border rounded p-3 h-100">
                    <h6 class="mb-2">Quản lý đơn hàng</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.order.index') }}" class="btn btn-sm btn-outline-primary">Danh sách / lọc</a>
                        <a href="{{ route('admin.order.index') }}" class="btn btn-sm btn-outline-success">Tạo đơn</a>
                        <a href="{{ route('admin.order.index') }}" class="btn btn-sm btn-outline-secondary">Giao hàng / thanh toán</a>
                        <a href="{{ route('admin.order.index') }}" class="btn btn-sm btn-outline-dark">Hoàn trả / lịch sử</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="border rounded p-3 h-100">
                    <h6 class="mb-2">Quản lý khách hàng</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.customer.index') }}" class="btn btn-sm btn-outline-primary">Danh sách / lọc</a>
                        <a href="{{ route('admin.customer.index') }}" class="btn btn-sm btn-outline-success">Thêm mới</a>
                        <a href="{{ route('admin.customer.index') }}" class="btn btn-sm btn-outline-secondary">Hạng / điểm thưởng</a>
                        <a href="{{ route('admin.customer.index') }}" class="btn btn-sm btn-outline-dark">Khôi phục / xóa mềm</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="border rounded p-3 h-100">
                    <h6 class="mb-2">Hệ thống & báo cáo</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.staff.index') }}" class="btn btn-sm btn-outline-primary">Nhân viên</a>
                        <a href="{{ route('admin.change-password') }}" class="btn btn-sm btn-outline-secondary">Đổi mật khẩu</a>
                        <a href="{{ route('admin.auth-activity-logs') }}" class="btn btn-sm btn-outline-dark">Nhật ký hoạt động</a>
                        <a href="{{ route('admin.dashboard.export-revenue', ['dateFrom' => request('dateFrom')]) }}" class="btn btn-sm btn-outline-success">Xuất báo cáo</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.dashboard') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="dateFrom" class="form-label">Kỳ báo cáo</label>
                <input type="text" class="form-control flatpickr-range" id="dateFrom" name="dateFrom" value="{{ \Carbon\Carbon::parse($filters['from'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filters['to'])->format('d/m/Y') }}" placeholder="Chọn ngày...">
            </div>
            <div class="col-md-9 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Lọc</button>
                <a class="btn btn-secondary me-2" href="{{ route('admin.dashboard') }}">Đặt lại</a>
                <a class="btn btn-success" href="{{ route('admin.dashboard.export-revenue', ['dateFrom' => request('dateFrom')]) }}">Xuất Excel</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card"><div class="card-body">
            <small class="text-muted">Tổng đơn</small>
            <h4 class="mb-0">{{ number_format($summary['orders_total']) }}</h4>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-body">
            <small class="text-muted">Đơn đã giao</small>
            <h4 class="mb-0">{{ number_format($summary['delivered_total']) }}</h4>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-body">
            <small class="text-muted">Doanh thu đã thanh toán</small>
            <h4 class="mb-0">{{ number_format($summary['paid_revenue'], 0, ',', '.') }} đ</h4>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Doanh thu theo ngày</h6></div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Ngày</th><th>Số đơn</th><th>Doanh thu</th></tr></thead>
                    <tbody>
                        @forelse($revenueRows as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                                <td>{{ number_format($row['orders_count']) }}</td>
                                <td>{{ number_format($row['revenue'], 0, ',', '.') }} đ</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center">Không có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Sản phẩm bán chạy</h6></div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Sản phẩm</th><th>SL bán</th><th>Doanh thu</th></tr></thead>
                    <tbody>
                        @forelse($topProducts as $item)
                            <tr>
                                <td>{{ $item['name'] }}</td>
                                <td>{{ number_format($item['sold_qty']) }}</td>
                                <td>{{ number_format($item['sold_revenue'], 0, ',', '.') }} đ</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center">Không có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header"><h6 class="mb-0">Khách hàng tiềm năng</h6></div>
    <div class="card-body table-responsive">
        <table class="table table-striped">
            <thead><tr><th>Khách hàng</th><th>Điện thoại</th><th>Số đơn</th><th>Tổng chi tiêu</th></tr></thead>
            <tbody>
                @forelse($topCustomers as $item)
                    <tr>
                        <td>{{ $item['full_name'] }}</td>
                        <td>{{ $item['phone'] }}</td>
                        <td>{{ number_format($item['orders_count']) }}</td>
                        <td>{{ number_format($item['total_spent'], 0, ',', '.') }} đ</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">Không có dữ liệu</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
