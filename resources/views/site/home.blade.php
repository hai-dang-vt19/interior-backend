@extends('site.base')

{{-- Style SCSS (nested): resources/scss/pages/_site-home.scss — import qua resources/scss/custom.scss --}}
@section('content')
    {{-- Hero + slider danh mục; danh sách SP + lọc trong .container bên dưới --}}
    <div class="site-full-header mb-4">
        <div class="site-hero">
            <div class="site-hero_title">
                <h1 class="h2">Chung Si Interior</h1>
            </div>
            <div class="site-hero_swiper_front">
                <div class="swiper" id="site-hero-swiper-front">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="site-hero-slide">
                                <div class="site-hero-visual" aria-hidden="true">
                                    <img src="https://picsum.photos/200/300" alt="">
                                </div>
                                <p class="site-hero-text">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap</p>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="site-hero-slide">
                                <div class="site-hero-visual" aria-hidden="true">
                                    <img src="https://picsum.photos/200/300" alt="">
                                </div>
                                <p class="site-hero-text">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="site-hero_swiper_back">
                <div class="swiper" id="site-hero-swiper-back">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="site-hero-slide">
                                <div class="site-hero-visual" aria-hidden="true">
                                    <img src="https://picsum.photos/200/300" alt="">
                                </div>
                                <p class="site-hero-text">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap</p>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="site-hero-slide">
                                <div class="site-hero-visual" aria-hidden="true">
                                    <img src="https://picsum.photos/200/300" alt="">
                                </div>
                                <p class="site-hero-text">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @foreach ($homeCategorySlides as $index => $block)
            @php($cat = $block['category'])
            @php($catProducts = $block['products'])
            <section class="site-home-category-block py-4">
                <div class="container">
                    <h2 class="h5 site-section-title mb-3">{{ $cat->name }}</h2>
                </div>
                <div class="container">
                    @if ($catProducts->isEmpty())
                        <p class="site-muted small mb-0">Chưa có sản phẩm trong danh mục này.</p>
                    @else
                        <div class="swiper site-category-swiper site-category-swiper--{{ $index }}" data-category-swiper>
                            <div class="swiper-wrapper">
                                @foreach ($catProducts as $product)
                                    @php($mainImageUrl = \App\Models\ProductImage::resolvePublicUrl(optional($product->images->firstWhere('is_primary', true))->image_url ?: optional($product->images->first())->image_url))
                                    <div class="swiper-slide">
                                        <a href="{{ route('site.products.show', $product->id) }}" class="text-decoration-none text-body">
                                            <div class="site-category-card">
                                                @if ($mainImageUrl)
                                                    <div class="site-category-card-img-wrap">
                                                        <img src="{{ $mainImageUrl }}" class="site-category-card-img" alt="{{ $product->name }}" loading="lazy">
                                                    </div>
                                                @else
                                                    <div class="site-category-card-placeholder">{{ $product->name }}</div>
                                                @endif
                                                <div class="site-category-card-body">
                                                    <span class="small fw-semibold d-block text-truncate">{{ $product->name }}</span>
                                                    <span class="small site-muted">{{ number_format((float) ($product->discount_price ?? $product->price), 0, ',', '.') }} đ</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endforeach
    </div>

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
            @php($mainImageUrl = \App\Models\ProductImage::resolvePublicUrl(optional($product->images->firstWhere('is_primary', true))->image_url ?: optional($product->images->first())->image_url))
            <div class="col-md-3 col-sm-6">
                <div class="card h-100 site-product-card">
                    @if ($mainImageUrl)
                        <img src="{{ $mainImageUrl }}" class="card-img-top site-product-img site-skeleton-image" alt="{{ $product->name }}" loading="lazy" onload="this.classList.add('loaded')">
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
                            <button
                                type="button"
                                class="btn btn-sm btn-primary w-100 btn-add-to-cart-ajax"
                                data-product-id="{{ $product->id }}"
                                data-quantity="1"
                            >
                                Thêm vào giỏ
                            </button>
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

@section('scripts')
<script type="module">
    $(document).ready(function () {
        $('.btn-add-to-cart-ajax').on('click', function () {
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
