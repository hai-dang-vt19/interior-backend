@extends('site.base')

@section('content')
    @php($mainImageUrl = \App\Models\ProductImage::resolvePublicUrl(optional($product->images->firstWhere('is_primary', true))->image_url ?: optional($product->images->first())->image_url))

    <div class="mb-3">
        <a href="{{ route('site.home') }}" class="text-decoration-none">&larr; Quay lại trang sản phẩm</a>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            @if ($mainImageUrl)
                <img src="{{ $mainImageUrl }}" class="img-fluid rounded border shadow-sm site-skeleton-image image-product" alt="{{ $product->name }}" loading="lazy" onload="this.classList.add('loaded')">
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

    <section id="product-reviews" class="site-product-reviews mt-5 pt-4 border-top">
        <h5 class="site-section-title mb-3">Đánh giá từ khách hàng</h5>

        @auth('customer')
            @if ($canReviewAfterPurchase)
                <div class="card site-panel mb-4">
                    <div class="card-body">
                        @if ($myProductReview)
                            <h6 class="mb-3">Sửa đánh giá của bạn</h6>
                            <form action="{{ route('site.products.reviews.update', [$product->id, $myProductReview->id]) }}" method="POST" class="site-product-review-form">
                                @csrf
                                @method('PATCH')
                                @if ($errors->any())
                                    <div class="alert alert-danger small mb-3">{{ $errors->first() }}</div>
                                @endif
                                <div class="mb-3">
                                    <label class="form-label">Số sao</label>
                                    <select name="rating" class="form-select @error('rating') is-invalid @enderror" required>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" @selected((int) old('rating', $myProductReview->rating) === $i)>{{ $i }} sao</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nội dung</label>
                                    <textarea name="review" class="form-control @error('review') is-invalid @enderror" rows="4" required>{{ old('review', $myProductReview->review) }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-dark">Cập nhật đánh giá</button>
                            </form>
                        @else
                            <h6 class="mb-3">Viết đánh giá</h6>
                            <form action="{{ route('site.products.reviews.store', $product->id) }}" method="POST" class="site-product-review-form">
                                @csrf
                                @if ($errors->any())
                                    <div class="alert alert-danger small mb-3">{{ $errors->first() }}</div>
                                @endif
                                <div class="mb-3">
                                    <label class="form-label">Số sao</label>
                                    <select name="rating" class="form-select @error('rating') is-invalid @enderror" required>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" @selected((int) old('rating') === $i)>{{ $i }} sao</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nội dung</label>
                                    <textarea name="review" class="form-control @error('review') is-invalid @enderror" rows="4" required placeholder="Chia sẻ trải nghiệm của bạn">{{ old('review') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-dark">Gửi đánh giá</button>
                            </form>
                        @endif
                    </div>
                </div>
            @else
                <p class="text-muted small mb-4">Bạn chỉ có thể đánh giá khi đã mua sản phẩm và đơn hàng được ghi nhận là <strong>đã giao</strong>.</p>
            @endif
        @else
            <p class="small mb-4"><a href="{{ route('site.login') }}">Đăng nhập</a> để đánh giá sản phẩm (sau khi đơn hàng đã giao).</p>
        @endauth

        <div class="site-product-review-list d-flex flex-column gap-3">
            @forelse ($productReviews as $rev)
                <article class="site-product-review-item card site-panel">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                            <strong>{{ $rev->customer?->full_name ?? 'Khách hàng' }}</strong>
                            <span class="site-product-review-stars text-warning" aria-label="{{ $rev->rating }} trên 5 sao">
                                @for ($s = 1; $s <= 5; $s++)
                                    {{ $s <= (int) $rev->rating ? '★' : '☆' }}
                                @endfor
                            </span>
                        </div>
                        <p class="mb-1 small text-muted">{{ $rev->created_at?->format('d/m/Y H:i') }}</p>
                        <p class="mb-0 site-product-review-text">{!! nl2br(e($rev->review)) !!}</p>
                    </div>
                </article>
            @empty
                <p class="text-muted mb-0">Chưa có đánh giá nào cho sản phẩm này.</p>
            @endforelse
        </div>
    </section>

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
