@extends('site.base')

@section('content')
    @php($categoryItems = collect($categories ?? []))
    @php($homeCategorySlideItems = collect($homeCategorySlides ?? []))

    <section class="nx-home">
        <div class="nx-topline mb-3 d-flex justify-content-between flex-wrap gap-2">
            <span>Nội thất cao cấp</span>
            <span>HOTLINE: 0947 508 288</span>
            <span>Thiết kế nội thất toàn diện</span>
        </div>

        <div class="nx-hero-grid mb-4">
            <div class="swiper nx-hero-main nx-hero-main-swiper">
                <div class="swiper-wrapper">
                    @forelse (($heroBannerBySide['left'] ?? collect()) as $leftBanner)
                        @php($heroProduct = $leftBanner->product ?? null)
                        @php($heroImage = $heroProduct ? \App\Models\ProductImage::resolvePublicUrl(optional($heroProduct->images->firstWhere('is_primary', true))->image_url ?: optional($heroProduct->images->first())->image_url) : asset('storage/images/image_default.jpg'))
                        <div class="swiper-slide">
                            <a href="{{ $heroProduct ? route('site.products.show', $heroProduct->id) : route('site.products.index') }}" class="nx-hero-main-link text-decoration-none">
                                <img src="{{ $heroImage }}" alt="{{ $heroProduct?->name ?? 'Nội thất cao cấp' }}">
                                <div class="nx-overlay">
                                    <h1 class="nx-title">{{ $heroProduct?->name ?? 'Không gian song định nghĩa bởi sự tinh gian' }}</h1>
                                    <p class="nx-sub">{{ $heroProduct?->description ?? 'Khám phá bộ sưu tập nội thất hiện đại cho phòng khách, phòng an và phòng ngủ.' }}</p>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="swiper-slide">
                            <a href="{{ route('site.products.index') }}" class="nx-hero-main-link text-decoration-none">
                                <img src="{{ asset('storage/images/image_default.jpg') }}" alt="Nội thất cao cấp">
                                <div class="nx-overlay">
                                    <h1 class="nx-title">Không gian song định nghĩa bởi sự tinh gian</h1>
                                    <p class="nx-sub">Khám phá bộ sưu tập nội thất hiện đại cho phòng khách, phòng an và phòng ngủ.</p>
                                </div>
                            </a>
                        </div>
                    @endforelse
                </div>
                <div class="swiper-pagination nx-hero-main-pagination"></div>
            </div>

            <div class="d-flex flex-column gap-3">
                @foreach ($homeCategorySlideItems->take(2) as $block)
                    @php($cat = $block['category'])
                    @php($first = collect($block['products'] ?? [])->first())
                    @php($firstImage = \App\Models\ProductImage::resolvePublicUrl(optional($first?->images?->firstWhere('is_primary', true))->image_url ?: optional($first?->images?->first())->image_url))
                    <a href="{{ route('site.products.index', ['category_id' => $cat->id]) }}" class="nx-hero-side-card text-decoration-none">
                        <img src="{{ $firstImage ?: asset('storage/images/image_default.jpg') }}" alt="{{ $cat->name }}">
                        <div class="nx-overlay">
                            <h2 class="h5 mb-1 text-white">{{ $cat->name }}</h2>
                            <p class="nx-sub mb-0">Xem bộ sưu tập</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mb-4">
            <div class="nx-chip-row">
                @foreach ($categoryItems->take(12) as $category)
                    <a href="{{ route('site.products.index', ['category_id' => $category->id]) }}" class="nx-chip">{{ $category->name }}</a>
                @endforeach
            </div>
        </div>

        <div class="card mb-4 border-0 nx-filter-card">
            <div class="card-body">
                <form class="row g-2" method="GET" action="{{ route('site.home') }}">
                    <div class="col-lg-5">
                        <input type="text" class="form-control" name="keyword" value="{{ $keyword }}" placeholder="Tim kiem san pham...">
                    </div>
                    <div class="col-lg-4">
                        <select class="form-select" name="category_id">
                            <option value="0">Tất cả danh mục</option>
                            @foreach ($categoryItems as $category)
                                <option value="{{ $category->id }}" {{ $categoryId === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 d-grid">
                        <button class="btn btn-dark" type="submit">Lọc sản phẩm</button>
                    </div>
                </form>
            </div>
        </div>

        <section class="mb-4">
            <h2 class="nx-sec-title">Sản phẩm mới</h2>
            <div class="row g-3">
                @forelse ($products as $product)
                    <div class="col-xl-3 col-md-4 col-sm-6">
                        @include('site.component.product-card', ['product' => $product])
                    </div>
                @empty
                    <div class="col-12">
                        @include('site.component.empty-state', [
                            'title' => 'Chưa có sản phẩm phù hợp',
                            'description' => 'Hãy thử thay đổi từ khóa hoặc danh mục tìm kiếm.',
                            'actionUrl' => route('site.home'),
                            'actionText' => 'Xóa bộ lọc.',
                        ])
                    </div>
                @endforelse
            </div>
            <div class="mt-4">
                {{ $products->links('vendor.pagination.bootstrap-5') }}
            </div>
        </section>

        <section class="nx-idea-wrap">
            <div class="row g-4">
                <div class="col-lg-6">
                    <h2 class="nx-sec-title mb-2">Về Chung Si Interior</h2>
                    <p class="mb-2">Thương hiệu nội thất hướng đến không gian song tinh tế, tiện nghi và bề vực. Chúng tôi mang đến giải pháp nội thất trọn gói cho gia đình hiện đại.</p>
                    <a href="{{ route('site.products.index') }}" class="btn btn-outline-dark btn-sm mt-2">Khám phá bộ sưu tập</a>
                </div>
                <div class="col-lg-6">
                    <h3 class="h5 mb-2">Ý tưởng cảm hứng</h3>
                    <div class="swiper nx-idea-swiper">
                        <div class="swiper-wrapper">
                            @forelse (collect($heroBannerBySide['right'] ?? [])->take(3) as $rightBanner)
                                @php($heroProduct = $rightBanner->product ?? null)
                                @php($heroImage = $heroProduct ? \App\Models\ProductImage::resolvePublicUrl(optional($heroProduct->images->firstWhere('is_primary', true))->image_url ?: optional($heroProduct->images->first())->image_url) : asset('storage/images/image_default.jpg'))
                                <div class="swiper-slide">
                                    <a href="{{ $heroProduct ? route('site.products.show', $heroProduct->id) : route('site.products.index') }}" class="nx-hero-side-card nx-idea-slide-card text-decoration-none">
                                        <img src="{{ $heroImage }}" alt="{{ $heroProduct?->name ?? 'Nội thất' }}">
                                        <div class="nx-overlay">
                                            <h2 class="h5 mb-1 text-white">{{ $heroProduct?->name ?? 'Sản phẩm nổi bật' }}</h2>
                                            <p class="nx-sub mb-0">{{ $heroProduct?->description ?? 'Gợi ý bố trí và phong cách nội thất phù hợp không gian của bạn.' }}</p>
                                        </div>
                                    </a>
                                </div>
                            @empty
                                <div class="swiper-slide">
                                    <a href="{{ route('site.products.index') }}" class="nx-hero-side-card nx-idea-slide-card text-decoration-none">
                                        <img src="{{ asset('storage/images/image_default.jpg') }}" alt="Nội thất">
                                        <div class="nx-overlay">
                                            <h2 class="h5 mb-1 text-white">Khám phá danh mục</h2>
                                            <p class="nx-sub mb-0">Xem bộ sưu tập nội thất và gợi ý bố trí từ Chung Si Interior.</p>
                                        </div>
                                    </a>
                                </div>
                            @endforelse
                        </div>
                        <div class="swiper-pagination nx-idea-pagination"></div>
                    </div>
                </div>
            </div>
        </section>
    </section>
@endsection

@section('scripts')
<script type="module">
    $(document).ready(function () {
        if (typeof window.Swiper !== 'undefined') {
            const heroSlideCount = document.querySelectorAll('.nx-hero-main-swiper .swiper-slide').length;
            const canLoop = heroSlideCount > 1;
            new Swiper('.nx-hero-main-swiper', {
                loop: canLoop,
                autoplay: canLoop
                    ? {
                        delay: 4000,
                        disableOnInteraction: false,
                    }
                    : false,
                pagination: {
                    el: '.nx-hero-main-pagination',
                    clickable: true,
                },
            });

            const ideaSlides = document.querySelectorAll('.nx-idea-swiper .swiper-slide').length;
            const ideaCanLoop = ideaSlides > 1;
            new Swiper('.nx-idea-swiper', {
                loop: ideaCanLoop,
                autoplay: ideaCanLoop
                    ? {
                        delay: 4500,
                        disableOnInteraction: false,
                    }
                    : false,
                pagination: {
                    el: '.nx-idea-pagination',
                    clickable: true,
                },
                effect: 'fade',
                fadeEffect: { crossFade: true },
            });
        }

        const navigateToDetail = (el) => {
            const href = el?.dataset?.href;
            if (href) {
                window.location.href = href;
            }
        };

        $('.nx-product-card-link').on('click', function (event) {
            if ($(event.target).closest('.nx-no-card-nav').length) {
                return;
            }
            navigateToDetail(this);
        });

        $('.nx-product-card-link').on('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                navigateToDetail(this);
            }
        });

        $('.btn-add-to-cart-ajax').on('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            const $btn = $(this);
            const originalText = $btn.text();
            const productId = Number($btn.data('product-id'));
            const quantity = Number($btn.data('quantity') || 1);
            const variantIdRaw = $btn.data('product-variant-id');
            const productVariantId = variantIdRaw !== undefined && variantIdRaw !== null && String(variantIdRaw).trim() !== ''
                ? Number(variantIdRaw)
                : null;

            $btn.prop('disabled', true).text('Đang thêm...');

            $.ajax({
                url: @json(route('site.cart.items.store')),
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': @json(csrf_token()),
                },
                data: {
                    product_id: productId,
                    quantity: quantity,
                    ...(productVariantId && !Number.isNaN(productVariantId) ? { product_variant_id: productVariantId } : {}),
                },
            }).done((res) => {
                if (window.Alert?.success) {
                    Alert.success(res?.message || 'Đã thêm vào giỏ hàng');
                }
            }).fail((xhr) => {
                const message = xhr?.responseJSON?.message || 'Không thể thêm vào giỏ hàng';
                if (window.Alert?.error) {
                    Alert.error(message);
                }
            }).always(() => {
                $btn.prop('disabled', false).text(originalText);
            });
        });
    });
</script>
@endsection
