@extends('site.base')

@section('content')
    @php($categoryItems = collect($categories ?? []))

    <section class="np-products-page">
        <div class="np-head mb-4">
            <p class="np-breadcrumb mb-1">
                <a href="{{ route('site.home') }}">Trang chủ</a>
                <span>/</span>
                <strong>Sản phẩm</strong>
            </p>
            <h1 class="np-title mb-2">Sản phẩm</h1>
            <p class="np-result mb-0">Đang hiển thị {{ $products->count() }} / {{ $products->total() }} sản phẩm</p>
        </div>

        <div class="row g-4">
            <aside class="col-lg-3">
                <div class="np-filter-panel">
                    <h2 class="h6 mb-3">Bộ lọc tìm kiếm</h2>
                    <form method="GET" action="{{ route('site.products.index') }}" class="d-grid gap-3">
                        <div>
                            <label class="form-label mb-1">Từ khóa</label>
                            <input
                                type="text"
                                name="keyword"
                                value="{{ $keyword }}"
                                class="form-control"
                                placeholder="Tên sản phẩm..."
                            >
                        </div>

                        <div>
                            <label class="form-label mb-1">Danh mục</label>
                            <select class="form-select" name="category_id">
                                <option value="0">Tất cả danh mục</option>
                                @foreach ($categoryItems as $category)
                                    <option value="{{ $category->id }}" {{ $categoryId === $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label mb-1">Giá từ</label>
                                <input
                                    type="number"
                                    name="min_price"
                                    min="0"
                                    step="1000"
                                    class="form-control"
                                    value="{{ $minPrice }}"
                                    placeholder="0"
                                >
                            </div>
                            <div class="col-6">
                                <label class="form-label mb-1">Đến</label>
                                <input
                                    type="number"
                                    name="max_price"
                                    min="0"
                                    step="1000"
                                    class="form-control"
                                    value="{{ $maxPrice }}"
                                    placeholder="10000000"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="form-label mb-1">Sắp xếp</label>
                            <select class="form-select" name="sort">
                                <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                                <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Giá: Thấp đến cao</option>
                                <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Giá: Cao đến thấp</option>
                                <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Tên: A-Z</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-dark" type="submit">Áp dụng</button>
                            <a href="{{ route('site.products.index') }}" class="btn btn-outline-secondary">Xóa bộ lọc</a>
                        </div>
                    </form>
                </div>
            </aside>

            <div class="col-lg-9">
                <div class="row g-3">
                    @forelse ($products as $product)
                        <div class="col-xl-4 col-md-6">
                            @include('site.component.product-card', ['product' => $product])
                        </div>
                    @empty
                        <div class="col-12">
                            @include('site.component.empty-state', [
                                'title' => 'Không tìm thấy sản phẩm',
                                'description' => 'Hãy thử thay đổi từ khóa, danh mục hoặc khoảng giá.',
                                'actionUrl' => route('site.products.index'),
                                'actionText' => 'Xóa bộ lọc',
                            ])
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $products->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
<script type="module">
    $(document).ready(function () {
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
