@props(['product'])

@php($mainImageUrl = \App\Models\ProductImage::resolvePublicUrl(optional($product->images->firstWhere('is_primary', true))->image_url ?: optional($product->images->first())->image_url))
@php($activeVariants = ($product->relationLoaded('variants') ? $product->variants : collect())->filter(fn ($v) => (bool) $v->is_active))
@php($defaultVariantForCart = $activeVariants->firstWhere('is_default', true) ?? $activeVariants->first())
@php($cardUnitPrice = \App\Support\ProductLinePricing::unitTotal($product, $defaultVariantForCart))
@php($stockDisplay = \App\Support\ProductStock::displayUnits($product, $defaultVariantForCart))

<div
    class="nx-product-card h-100 nx-product-card-link"
    role="link"
    tabindex="0"
    data-href="{{ route('site.products.show', $product->id) }}"
    aria-label="Xem chi tiet {{ $product->name }}"
>
    <img src="{{ $mainImageUrl ?: asset('storage/images/image_default.jpg') }}" class="nx-product-img" alt="{{ $product->name }}" loading="lazy">
    <div class="nx-product-body d-flex flex-column">
        <small class="text-muted mb-1">{{ $product->category?->name ?? 'Chưa phân loại' }}</small>
        <h3 class="h6 mb-2">{{ $product->name }}</h3>
        <div class="nx-price mb-3">{{ number_format($cardUnitPrice, 0, ',', '.') }} đ</div>
        <small class="text-muted mb-3">Tồn kho: {{ (int) $stockDisplay }}@if($defaultVariantForCart) <span class="text-muted">(phiên bản mặc định)</span>@endif</small>
        <div class="mt-auto d-grid gap-2">
            <a href="{{ route('site.products.show', $product->id) }}" class="btn btn-outline-dark btn-sm nx-no-card-nav">Xem chi tiết</a>
            @if (auth()->guard('customer')->check())
                <button
                    type="button"
                    class="btn btn-dark btn-sm btn-add-to-cart-ajax nx-no-card-nav"
                    data-product-id="{{ $product->id }}"
                    data-product-variant-id="{{ $defaultVariantForCart?->id ?? '' }}"
                    data-quantity="1"
                >
                    Thêm vào giỏ hàng
                </button>
            @else
                <button type="button" class="btn btn-dark btn-sm nx-no-card-nav js-open-auth-modal" data-auth-tab="login">Đăng nhập để mua</button>
            @endif
        </div>
    </div>
</div>
