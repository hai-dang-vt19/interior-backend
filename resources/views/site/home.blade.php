@extends('site.base')

@section('content')
    <div class="d-flex justify-content-between align-items-center site-page-head">
        <div>
            <h4 class="mb-0 site-section-title">Sản phẩm nội thất</h4>
            <small class="site-muted">Khám phá bộ sưu tập dành cho không gian sống hiện đại</small>
        </div>
    </div>

    <div class="card mb-4 site-filter-card">
        <div class="card-body">
            <form class="row g-2" method="GET" action="{{ route('site.home') }}">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="keyword" value="{{ $keyword }}" placeholder="Tìm kiếm theo tên sản phẩm">
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="category_id">
                        <option value="0">-- Tất cả danh mục --</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ $categoryId === $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-grid">
                    <button class="btn btn-primary" type="submit">Lọc sản phẩm</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        @forelse ($products as $product)
            @php($mainImage = $product->image_url ?: optional($product->images->firstWhere('is_primary', true))->image_url ?: optional($product->images->first())->image_url)
            <div class="col-md-3 col-sm-6">
                <div class="card h-100 site-product-card">
                    @if ($mainImage)
                        <img src="{{ asset('storage/' . $mainImage) }}" class="card-img-top site-product-img site-skeleton-image" alt="{{ $product->name }}" loading="lazy" onload="this.classList.add('loaded')">
                    @endif
                    <div class="card-body">
                        <div class="small mb-1">
                            <span class="site-badge site-badge-neutral">{{ $product->category?->name ?? 'Chưa phân loại' }}</span>
                        </div>
                        <h6 class="card-title">{{ $product->name }}</h6>
                        <div class="site-price">
                            {{ number_format((float) ($product->discount_price ?? $product->price), 0, ',', '.') }} đ
                        </div>
                        @php($stockPercent = min(100, max(3, (int) round(((int) $product->quantity / 100) * 100))))
                        <div class="mt-2">
                            <div class="site-stock-progress">
                                <div class="site-stock-progress-bar" style="width: {{ $stockPercent }}%"></div>
                            </div>
                            <small class="site-muted">Tồn kho: {{ (int) $product->quantity }}</small>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0 pb-3">
                        <a href="{{ route('site.products.show', $product->id) }}" class="btn btn-sm btn-outline-dark w-100 mb-2">Xem chi tiết</a>
                        @if (auth()->guard('customer')->check())
                            <form action="{{ route('site.cart.items.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-sm btn-primary w-100">Thêm vào giỏ</button>
                            </form>
                        @else
                            <a href="{{ route('site.login') }}" class="btn btn-sm btn-primary w-100">Đăng nhập để mua</a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                @include('site.component.empty-state', [
                    'title' => 'Chưa có sản phẩm phù hợp',
                    'description' => 'Hãy thử thay đổi từ khóa hoặc danh mục tìm kiếm.',
                    'actionUrl' => route('site.home'),
                    'actionText' => 'Xóa bộ lọc',
                ])
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
@endsection
