@extends('base')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.customer.index') }}">Khách hàng</a></li>
            <li class="breadcrumb-item active" aria-current="page">Địa chỉ & liên hệ</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">Khách hàng: {{ $customer->full_name }} ({{ $customer->phone }})</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4"><strong>Hạng:</strong> {{ $customer->formatLoyaltyTier() }}</div>
            <div class="col-md-4"><strong>Điểm:</strong> {{ number_format((int) $customer->reward_points) }}</div>
            <div class="col-md-4"><strong>Ưu đãi:</strong> {{ $customer->getLoyaltyBenefit() }}</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Quản lý địa chỉ</h6></div>
            <div class="card-body">
                <form action="{{ route('admin.customer.address.store', $customer->id) }}" method="POST" class="row g-2 mb-3">
                    @csrf
                    <div class="col-12">
                        <input type="text" class="form-control" name="address_line" placeholder="Địa chỉ cụ thể">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="city" placeholder="Tỉnh/Thành phố">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="district" placeholder="Quận/Huyện">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="ward" placeholder="Phường/Xã">
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default">
                            <label class="form-check-label" for="is_default">Mặc định</label>
                        </div>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-success">Thêm</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr><th>ID</th><th>Địa chỉ</th><th>Mặc định</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse($customer->addresses as $address)
                                <tr>
                                    <td>{{ $address->id }}</td>
                                    <td>
                                        {{ $address->address_line }},
                                        {{ $address->ward }},
                                        {{ $address->district }},
                                        {{ $address->city }}
                                    </td>
                                    <td>{{ $address->is_default ? 'Có' : 'Không' }}</td>
                                    <td class="text-end">
                                        <form action="{{ route('admin.customer.address.destroy', [$customer->id, $address->id]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger btn-delete-address">Xóa</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">Chưa có địa chỉ</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Thông báo & liên hệ</h6></div>
            <div class="card-body">
                <form action="{{ route('admin.customer.contact.store', $customer->id) }}" method="POST" class="row g-2 mb-3">
                    @csrf
                    <div class="col-md-4">
                        <select class="form-select" name="channel">
                            <option value="phone">Phone</option>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                            <option value="zalo">Zalo</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="title" placeholder="Tiêu đề liên hệ">
                    </div>
                    <div class="col-12">
                        <textarea class="form-control" name="message" rows="3" placeholder="Nội dung liên hệ/thông báo"></textarea>
                    </div>
                    <div class="col-12 d-grid">
                        <button type="submit" class="btn btn-primary">Ghi nhận liên hệ</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr><th>Kênh</th><th>Tiêu đề</th><th>Nội dung</th><th>Nhân viên</th><th>Thời gian</th></tr>
                        </thead>
                        <tbody>
                            @forelse($customer->contactLogs as $log)
                                <tr>
                                    <td>{{ strtoupper($log->channel) }}</td>
                                    <td>{{ $log->title }}</td>
                                    <td>{{ $log->message }}</td>
                                    <td>{{ $log->contactedBy?->full_name ?? '-' }}</td>
                                    <td>{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">Chưa có lịch sử liên hệ</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="module">
$(document).ready(function() {
    $('.btn-delete-address').on('click', function() {
        $(this).closest('form').submit();
    });
});
</script>
@endsection
