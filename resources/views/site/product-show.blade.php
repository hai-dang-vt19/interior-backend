@extends('site.base')

@section('content')
    @php($mainImageUrl = \App\Models\ProductImage::resolvePublicUrl(optional($product->images->firstWhere('is_primary', true))->image_url ?: optional($product->images->first())->image_url))
    @php($initialVariant = $product->variants->isNotEmpty() ? ($product->variants->firstWhere('is_default', true) ?? $product->variants->first()) : null)
    @php($productBaseUnit = \App\Support\ProductLinePricing::baseUnit($product))
    @php($displayPrice = \App\Support\ProductLinePricing::unitTotal($product, $initialVariant))
    @php($listPrice = (float) $product->price)
    @php($hasDiscount = $productBaseUnit + 0.009 < $listPrice)
    @php($basePrice = $listPrice)
    @php($selectedStockAvail = \App\Support\ProductStock::unitsAvailable($product, $initialVariant?->id))
    @php($stockPercent = min(100, max(3, (int) round(($selectedStockAvail / 100) * 100))))

    <section class="spd-page">
        <div class="spd-breadcrumb mb-3">
            <a href="{{ route('site.products.index') }}" class="text-decoration-none">&larr; Quay lại danh sách sản phẩm</a>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-lg-6">
                <div class="spd-gallery-card">
                    @if ($mainImageUrl)
                        <img src="{{ $mainImageUrl }}" class="img-fluid site-skeleton-image spd-main-image" alt="{{ $product->name }}" loading="lazy" onload="this.classList.add('loaded')">
                    @else
                        <img src="{{ asset('storage/images/image_default.jpg') }}" class="img-fluid site-skeleton-image spd-main-image" alt="{{ $product->name }}" loading="lazy" onload="this.classList.add('loaded')">
                    @endif
                </div>
            </div>

            <div class="col-lg-6">
                <article class="spd-info-card">
                    <div class="mb-2">
                        <span class="site-badge site-badge-neutral">{{ $product->category?->name ?? 'Chưa phân loại' }}</span>
                    </div>
                    <h1 class="spd-title">{{ $product->name }}</h1>
                    @if ($product->sku)
                        <p class="spd-sku">SKU: {{ $product->sku }}</p>
                    @endif

                    <div class="spd-price-wrap mb-3">
                        <div class="spd-price-current" data-spd-main-price>{{ number_format($displayPrice, 0, ',', '.') }} đ</div>
                        @if ($hasDiscount)
                            <div class="spd-price-old">{{ number_format($basePrice, 0, ',', '.') }} đ</div>
                        @endif
                    </div>
                    <div class="spd-price-wrap mb-3">
                        @if ($initialVariant && \App\Support\ProductLinePricing::variantAddon($initialVariant) > 0)
                            <p class="small text-muted mb-0 mt-2 mb-0">
                                Gồm giá sản phẩm {{ number_format($productBaseUnit, 0, ',', '.') }} đ
                                + phụ phí phiên bản {{ number_format(\App\Support\ProductLinePricing::variantAddon($initialVariant), 0, ',', '.') }} đ
                            </p>
                        @endif
                    </div>

                    <div class="spd-meta-list mb-3">
                        @if ($product->style)
                            <span class="site-badge site-badge-neutral">Phong cách: {{ $product->style }}</span>
                        @endif
                        @if ($product->space_type)
                            <span class="site-badge site-badge-neutral">Không gian: {{ $product->space_type }}</span>
                        @endif
                        @if ($product->origin)
                            <span class="site-badge site-badge-neutral">Xuất xứ: {{ $product->origin }}</span>
                        @endif
                        @if ($product->year_released)
                            <span class="site-badge site-badge-neutral">Năm: {{ $product->year_released }}</span>
                        @endif
                    </div>

                    <div class="spd-stock-box mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Tồn kho phiên bản: <span data-spd-stock-display>{{ (int) $selectedStockAvail }}</span></strong>
                        </div>
                        @if ($product->variants->isNotEmpty())
                            <p class="small text-muted mb-1 mt-2">Tổng các phiên bản: {{ (int) $product->variants->sum(fn ($v) => (int) $v->quantity) }}</p>
                        @endif
                        <div class="site-stock-progress mt-2">
                            <div class="site-stock-progress-bar" data-spd-stock-bar style="width: {{ $stockPercent }}%"></div>
                        </div>
                    </div>

                    <p class="mb-2">{{ $product->description_short ?: ($product->description ?: 'Chưa có mô tả sản phẩm.') }}</p>
                    @if ($product->description_long)
                        <p class="mb-3 text-muted">{{ $product->description_long }}</p>
                    @endif

                    <div class="spd-buy-box">
                        @if (auth()->guard('customer')->check())
                            <form action="{{ route('site.cart.items.store') }}" method="POST" class="row g-2 align-items-end" id="spd-add-cart-form" data-spd-base-unit="{{ $productBaseUnit }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                @if ($product->variants->isNotEmpty())
                                    <div class="col-12">
                                        <label class="form-label">Chọn phiên bản</label>
                                        <div class="list-group list-group-flush border rounded">
                                            @foreach ($product->variants as $variant)
                                                <label class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2 mb-0">
                                                    <span class="d-flex align-items-start gap-2">
                                                        <input
                                                            type="radio"
                                                            name="product_variant_id"
                                                            value="{{ $variant->id }}"
                                                            class="form-check-input mt-1 spd-variant-radio"
                                                            data-variant-addon="{{ (float) $variant->price }}"
                                                            data-variant-stock="{{ (int) $variant->quantity }}"
                                                            @checked($initialVariant && (int) $initialVariant->id === (int) $variant->id)
                                                            required
                                                        >
                                                        <span>
                                                            <span class="d-block small text-muted">{{ $variant->sku_variant ?: '—' }}</span>
                                                            <span class="d-block">{{ $variant->color_name ?: 'Màu —' }} @if($variant->material_main) · {{ $variant->material_main }} @endif</span>
                                                        </span>
                                                    </span>
                                                    @php($rowUnit = \App\Support\ProductLinePricing::unitTotal($product, $variant))
                                                    <span class="text-end">
                                                        <strong class="text-nowrap d-block">{{ number_format($rowUnit, 0, ',', '.') }} đ</strong>
                                                        @if (\App\Support\ProductLinePricing::variantAddon($variant) > 0)
                                                            <small class="text-muted text-nowrap d-block">+ {{ number_format(\App\Support\ProductLinePricing::variantAddon($variant), 0, ',', '.') }} đ phiên bản</small>
                                                        @endif
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-4">
                                    <label class="form-label">Số lượng</label>
                                    <input type="number" class="form-control" name="quantity" id="spd-qty-input" min="1" max="{{ max(1, (int) $selectedStockAvail) }}" value="1">
                                </div>
                                <div class="col-md-8">
                                    <button class="btn btn-primary w-100" type="submit">Thêm vào giỏ hàng</button>
                                </div>
                            </form>
                        @else
                            <button type="button" class="btn btn-primary w-100 js-open-auth-modal" data-auth-tab="login">Đăng nhập để mua hàng</button>
                        @endif
                    </div>
                </article>
            </div>
        </div>

        @if ($product->specs->isNotEmpty())
            <section class="spd-section mt-4">
                <div class="spd-section-head">
                    <h5 class="site-section-title mb-0">Thông số kỹ thuật</h5>
                </div>
                <div class="row g-2">
                    @foreach ($product->specs as $spec)
                        <div class="col-md-6">
                            <div class="spd-spec-item h-100">
                                <div class="small text-muted">{{ $spec->spec_group ?: 'Thông số' }}</div>
                                <div>
                                    <strong>{{ $spec->spec_key ?: 'Thông tin' }}:</strong>
                                    {{ $spec->spec_value ?: '-' }}{{ $spec->spec_unit ? ' '.$spec->spec_unit : '' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section id="product-reviews" class="site-product-reviews spd-section mt-5">
            <div class="spd-section-head">
                <h5 class="site-section-title mb-0">Đánh giá từ khách hàng</h5>
            </div>

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
                <p class="small mb-4"><button type="button" class="btn btn-link p-0 align-baseline js-open-auth-modal" data-auth-tab="login">Đăng nhập</button> để đánh giá sản phẩm (sau khi đơn hàng đã giao).</p>
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
            <section class="spd-section mt-4">
                <div class="spd-section-head">
                    <h5 class="mb-0 site-section-title">Sản phẩm liên quan</h5>
                </div>
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
            </section>
        @endif
    </section>
@endsection

@section('scripts')
    <script>
        (function () {
            const mainPriceEl = document.querySelector('[data-spd-main-price]');
            const buyForm = document.getElementById('spd-add-cart-form');
            if (!mainPriceEl || !buyForm) {
                return;
            }
            const baseUnit = Number(buyForm.getAttribute('data-spd-base-unit') || 0);
            const stockEl = document.querySelector('[data-spd-stock-display]');
            const stockBar = document.querySelector('[data-spd-stock-bar]');
            const qtyInput = document.getElementById('spd-qty-input');
            const formatVndInt = (n) => {
                const v = Math.max(0, Math.round(Number(n) || 0));
                return v.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' đ';
            };
            const applyStockUi = (unitsRaw) => {
                const u = Math.max(0, Math.floor(Number(unitsRaw) || 0));
                if (stockEl) {
                    stockEl.textContent = String(u);
                }
                if (stockBar) {
                    stockBar.style.width = `${Math.min(100, Math.max(3, Math.round((u / 100) * 100)))}%`;
                }
                if (qtyInput) {
                    const cap = Math.max(1, u);
                    qtyInput.max = String(cap);
                    const cur = Number(qtyInput.value || 1);
                    qtyInput.value = String(Math.min(Math.max(1, cur), cap));
                }
            };

            document.querySelectorAll('.spd-variant-radio').forEach((radio) => {
                radio.addEventListener('change', () => {
                    if (!radio.checked) {
                        return;
                    }
                    const addon = Number(radio.getAttribute('data-variant-addon') || 0);
                    mainPriceEl.textContent = formatVndInt(baseUnit + addon);
                    applyStockUi(radio.getAttribute('data-variant-stock'));
                });
            });
            const initRadio = document.querySelector('.spd-variant-radio:checked');
            if (initRadio) {
                applyStockUi(initRadio.getAttribute('data-variant-stock'));
            }
        })();
    </script>
@endsection
