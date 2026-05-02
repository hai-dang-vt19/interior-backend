@props([
    'product',
    'variant' => null,
    /** @var float|string|null Giá đã chốt trong giỏ/chi tiết đơn */
    'storedUnit' => null,
    /**
     * True: chỉ hiển thị mô tả phiên bản — không tái hiển thị đơn giá (tránh khác biệt với cột đơn giá trên đơn).
     */
    'orderLinePreview' => false,
])

@php
    use App\Support\ProductLinePricing;
    $productModel = $product;
    /** @var \App\Models\ProductVariant|null $v */
    $v = $variant;
@endphp

@if ($orderLinePreview)
    @if ($v !== null && ($summary = ProductLinePricing::variantSummary($v)))
        <div class="small text-muted mb-0 lh-sm">{{ $summary }}</div>
    @endif
@elseif ($productModel !== null && $v !== null)
    @php
        $base = ProductLinePricing::baseUnit($productModel);
        $addon = ProductLinePricing::variantAddon($v);
        $payable = $storedUnit !== null ? (float) $storedUnit : ProductLinePricing::unitTotal($productModel, $v);
    @endphp
    @if ($summary = ProductLinePricing::variantSummary($v))
        <div class="small text-muted mb-1">{{ $summary }}</div>
    @endif
    <div class="small text-muted lh-sm">
        Giá sản phẩm: <strong>{{ number_format($base, 0, ',', '.') }} đ</strong>
        @if ($addon > 0)
            · Phiên bản: <strong>+ {{ number_format($addon, 0, ',', '.') }} đ</strong>
        @endif
    </div>
    <div class="small text-muted lh-sm mt-1">Đơn giá áp dụng: <strong>{{ number_format($payable, 0, ',', '.') }} đ</strong></div>
@elseif ($v !== null && $storedUnit !== null)
    @if ($summary = ProductLinePricing::variantSummary($v))
        <div class="small text-muted mb-1">{{ $summary }}</div>
    @endif
    <div class="small text-muted lh-sm">Đơn giá (đã chốt): <strong>{{ number_format((float) $storedUnit, 0, ',', '.') }} đ</strong></div>
@elseif ($productModel !== null && $storedUnit !== null)
    <div class="small text-muted lh-sm">Đơn giá: <strong>{{ number_format((float) $storedUnit, 0, ',', '.') }} đ</strong></div>
@elseif ($storedUnit !== null)
    <div class="small text-muted lh-sm">Đơn giá: <strong>{{ number_format((float) $storedUnit, 0, ',', '.') }} đ</strong></div>
@endif
