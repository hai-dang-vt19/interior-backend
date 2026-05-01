@props(['product'])

@php($mainImageUrl = \App\Models\ProductImage::resolvePublicUrl(optional($product->images->firstWhere('is_primary', true))->image_url ?: optional($product->images->first())->image_url))

<div
    class="nx-product-card h-100 nx-product-card-link"
    role="link"
    tabindex="0"
    data-href="{{ route('site.products.show', $product->id) }}"
    aria-label="Xem chi tiet {{ $product->name }}"
>
    <img src="{{ $mainImageUrl ?: asset('storage/images/image_default.jpg') }}" class="nx-product-img" alt="{{ $product->name }}" loading="lazy">
    <div class="nx-product-body d-flex flex-column">
        <small class="text-muted mb-1">{{ $product->category?->name ?? 'Chua phan loai' }}</small>
        <h3 class="h6 mb-2">{{ $product->name }}</h3>
        <div class="nx-price mb-3">{{ number_format((float) ($product->discount_price ?? $product->price), 0, ',', '.') }} đ</div>
        <small class="text-muted mb-3">So luong: {{ (int) ($product->quantity ?? 0) }}</small>
        <div class="mt-auto d-grid gap-2">
            <a href="{{ route('site.products.show', $product->id) }}" class="btn btn-outline-dark btn-sm nx-no-card-nav">Xem chi tiet</a>
            @if (auth()->guard('customer')->check())
                <button
                    type="button"
                    class="btn btn-dark btn-sm btn-add-to-cart-ajax nx-no-card-nav"
                    data-product-id="{{ $product->id }}"
                    data-quantity="1"
                >
                    Them vao gio
                </button>
            @else
                <button type="button" class="btn btn-dark btn-sm nx-no-card-nav js-open-auth-modal" data-auth-tab="login">Dang nhap de mua</button>
            @endif
        </div>
    </div>
</div>
