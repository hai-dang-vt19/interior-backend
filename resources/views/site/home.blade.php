@extends('site.base')

@section('content')
    @php($categoryItems = collect($categories ?? []))
    @php($homeCategorySlideItems = collect($homeCategorySlides ?? []))

    <section class="nx-home">
        <div class="nx-topline mb-3 d-flex justify-content-between flex-wrap gap-2">
            <span>NOI THAT CAO CAP</span>
            <span>HOTLINE: 0903 884 358</span>
            <span>THIET KE NOI THAT TOAN DIEN</span>
        </div>

        <div class="nx-hero-grid mb-4">
            <div class="swiper nx-hero-main nx-hero-main-swiper">
                <div class="swiper-wrapper">
                    @forelse (($heroBannerBySide['left'] ?? collect()) as $leftBanner)
                        @php($heroProduct = $leftBanner->product ?? null)
                        @php($heroImage = $heroProduct ? \App\Models\ProductImage::resolvePublicUrl(optional($heroProduct->images->firstWhere('is_primary', true))->image_url ?: optional($heroProduct->images->first())->image_url) : asset('storage/images/image_default.jpg'))
                        <div class="swiper-slide">
                            <a href="{{ $heroProduct ? route('site.products.show', $heroProduct->id) : route('site.products.index') }}" class="nx-hero-main-link text-decoration-none">
                                <img src="{{ $heroImage }}" alt="{{ $heroProduct?->name ?? 'Noi that cao cap' }}">
                                <div class="nx-overlay">
                                    <h1 class="nx-title">{{ $heroProduct?->name ?? 'Khong gian song dinh nghia boi su tinh gian' }}</h1>
                                    <p class="nx-sub">{{ $heroProduct?->description ?? 'Kham pha bo suu tap noi that hien dai cho phong khach, phong an va phong ngu.' }}</p>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="swiper-slide">
                            <a href="{{ route('site.products.index') }}" class="nx-hero-main-link text-decoration-none">
                                <img src="{{ asset('storage/images/image_default.jpg') }}" alt="Noi that cao cap">
                                <div class="nx-overlay">
                                    <h1 class="nx-title">Khong gian song dinh nghia boi su tinh gian</h1>
                                    <p class="nx-sub">Kham pha bo suu tap noi that hien dai cho phong khach, phong an va phong ngu.</p>
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
                            <p class="nx-sub mb-0">Xem bo suu tap</p>
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
                            <option value="0">Tat ca danh muc</option>
                            @foreach ($categoryItems as $category)
                                <option value="{{ $category->id }}" {{ $categoryId === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 d-grid">
                        <button class="btn btn-dark" type="submit">Loc san pham</button>
                    </div>
                </form>
            </div>
        </div>

        <section class="mb-4">
            <h2 class="nx-sec-title">San pham moi</h2>
            <div class="row g-3">
                @forelse ($products as $product)
                    <div class="col-xl-3 col-md-4 col-sm-6">
                        @include('site.component.product-card', ['product' => $product])
                    </div>
                @empty
                    <div class="col-12">
                        @include('site.component.empty-state', [
                            'title' => 'Chua co san pham phu hop',
                            'description' => 'Hay thu thay doi tu khoa hoac danh muc tim kiem.',
                            'actionUrl' => route('site.home'),
                            'actionText' => 'Xoa bo loc',
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
                    <h2 class="nx-sec-title mb-2">Ve Chung Si Interior</h2>
                    <p class="mb-2">Thuong hieu noi that huong den khong gian song tinh te, tien nghi va ben vung. Chung toi mang den giai phap noi that tron goi cho gia dinh hien dai.</p>
                    <a href="{{ route('site.products.index') }}" class="btn btn-outline-dark btn-sm mt-2">Kham pha bo suu tap</a>
                </div>
                <div class="col-lg-6">
                    <h3 class="h5 mb-2">Goc cam hung</h3>
                    @foreach ($homeCategorySlideItems->take(3) as $block)
                        <div class="nx-idea-card">
                            <strong>{{ $block['category']->name }}</strong>
                            <p class="mb-0 text-muted">Goi y bo tri va lua chon noi that theo phong cach hien dai, toi gian va am cung.</p>
                        </div>
                    @endforeach
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
