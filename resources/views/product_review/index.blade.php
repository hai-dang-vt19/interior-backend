@extends('base')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Đánh giá sản phẩm</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.product-review.index') }}" method="GET" class="row g-3" id="searchFormProductReview" autocomplete="off">
            <div class="col-md-3">
                <label class="form-label">Mã sản phẩm</label>
                <input type="number" class="form-control" name="product_id" value="{{ request('product_id') }}" min="1" placeholder="Lọc theo ID SP">
            </div>
            <div class="col-md-5">
                <label class="form-label">Từ khóa (nội dung, khách, tên SP)</label>
                <input type="text" class="form-control" name="keyword" value="{{ request('keyword') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Bản ghi / trang</label>
                <select class="form-select" name="per_page">
                    @foreach (App\Enums\PerPage::cases() as $perPage)
                        <option value="{{ $perPage->value }}" {{ (int) request('per_page', 20) === $perPage->value ? 'selected' : '' }}>
                            {{ $perPage->value }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary me-2">Tìm kiếm</button>
                <a href="{{ route('admin.product-review.index') }}" class="btn btn-secondary">Đặt lại</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Danh sách đánh giá</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th class="text-center">ID</th>
                        <th>Sản phẩm</th>
                        <th>Khách hàng</th>
                        <th class="text-center">Sao</th>
                        <th>Nội dung</th>
                        <th>Ngày</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td class="text-center">{{ $review->id }}</td>
                            <td>
                                @if ($review->product)
                                    #{{ $review->product->id }} — {{ $review->product->name }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                {{ $review->customer?->full_name ?? '—' }}
                                @if ($review->customer?->email)
                                    <br><small class="text-muted">{{ $review->customer->email }}</small>
                                @endif
                            </td>
                            <td class="text-center">{{ $review->rating }}/5</td>
                            <td>{{ \Illuminate\Support\Str::limit(strip_tags($review->review), 120) }}</td>
                            <td>{{ $review->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.product-review.edit', $review->id) }}" class="btn btn-sm btn-primary me-1">Sửa</a>
                                <form action="{{ route('admin.product-review.destroy', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa đánh giá này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Không có dữ liệu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-3">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection
