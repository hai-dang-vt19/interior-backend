@extends('base')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Nhật ký đăng nhập/đăng xuất</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" class="form-control" name="keyword" placeholder="Tìm theo hành động / mô tả" value="{{ $keyword }}">
            </div>
            <div class="col-md-auto">
                <button class="btn btn-primary" type="submit">Lọc</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tài khoản</th>
                        <th>Hành động</th>
                        <th>Mô tả</th>
                        <th>IP</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>{{ $log->user?->full_name ?? 'N/A' }}</td>
                            <td>{{ $log->action }}</td>
                            <td>{{ $log->description }}</td>
                            <td>{{ $log->ip_address ?? 'N/A' }}</td>
                            <td>{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-3">Chưa có dữ liệu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $logs->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>
@endsection
