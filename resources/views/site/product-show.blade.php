@extends('site.base')

@section('content')
    @php($mainImageUrl = \App\Models\ProductImage::resolvePublicUrl(optional($product->images->firstWhere('is_primary', true))->image_url ?: optional($product->images->first())->image_url))

    <div class="mb-3">
        <a href="{{ route('site.home') }}" class="text-decoration-none">&larr; Quay lại trang sản phẩm</a>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            @if ($mainImageUrl)
                <img src="{{ $mainImageUrl }}" class="img-fluid rounded border shadow-sm site-skeleton-image" alt="{{ $product->name }}" loading="lazy" onload="this.classList.add('loaded')">
            @else
                <div class="border rounded p-5 text-center text-muted">Chưa có hình ảnh</div>
            @endif
        </div>
        <div class="col-md-7">
            <div class="mb-2">
                <span class="site-badge site-badge-neutral">{{ $product->category?->name ?? 'Chưa phân loại' }}</span>
            </div>
            <h3 class="site-section-title">{{ $product->name }}</h3>
            <h4 class="site-price mb-3">
                {{ number_format((float) ($product->discount_price ?? $product->price), 0, ',', '.') }} đ
            </h4>
            <div class="mb-3">
                <strong>Tồn kho:</strong> {{ (int) $product->quantity }}
                @php($stockPercent = min(100, max(3, (int) round(((int) $product->quantity / 100) * 100))))
                <div class="site-stock-progress mt-2">
                    <div class="site-stock-progress-bar" style="width: {{ $stockPercent }}%"></div>
                </div>
            </div>
            <p class="mb-0">{{ $product->description ?: 'Chưa có mô tả sản phẩm.' }}</p>
            <hr>
            @if (auth()->guard('customer')->check())
                <form action="{{ route('site.cart.items.store') }}" method="POST" class="row g-2 align-items-end">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <div class="col-md-4">
                        <label class="form-label">Số lượng</label>
                        <input type="number" class="form-control" name="quantity" min="1" max="{{ (int) $product->quantity }}" value="1">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary w-100" type="submit">Thêm vào giỏ hàng</button>
                    </div>
                </form>
            @else
                <a href="{{ route('site.login') }}" class="btn btn-primary">Đăng nhập để mua hàng</a>
            @endif
        </div>
    </div>

    @if ($relatedProducts->isNotEmpty())
        <hr class="my-4">
        <h5 class="mb-3 site-section-title">Sản phẩm liên quan</h5>
        <div class="row g-3">
            @foreach ($relatedProducts as $related)
                @php($relatedImageUrl = \App\Models\ProductImage::resolvePublicUrl(optional($related->images->firstWhere('is_primary', true))->image_url ?: optional($related->images->first())->image_url))
                <div class="col-md-3 col-sm-6">
                    <div class="card h-100 site-product-card">
                        @if ($relatedImageUrl)
                            <img src="{{ $relatedImageUrl }}" class="card-img-top site-product-img site-skeleton-image" alt="{{ $related->name }}" loading="lazy" onload="this.classList.add('loaded')">
                        @endif
                        <div class="card-body">
                            <h6 class="card-title">{{ $related->name }}</h6>
                            <div class="site-price">
                                {{ number_format((float) ($related->discount_price ?? $related->price), 0, ',', '.') }} đ
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0">
                            <a href="{{ route('site.products.show', $related->id) }}" class="btn btn-sm btn-outline-dark w-100">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
