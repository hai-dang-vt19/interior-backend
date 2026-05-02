<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;

/**
 * Logic giá bán storefront: đơn giá dòng = giá SP (ưu đãi nếu có) + phụ phí trong bản ghi variant.
 */
final class ProductLinePricing
{
    /** Giá 1 đơn vị sản phẩm sau KM (discount_price fallback price). */
    public static function baseUnit(Product $product): float
    {
        return (float) ($product->discount_price ?? $product->price);
    }

    /** Phụ phí của phiên bản / biến thể (cộng thêm vào giá SP). */
    public static function variantAddon(?ProductVariant $variant): float
    {
        return $variant === null ? 0.0 : max(0.0, (float) $variant->price);
    }

    /** Đơn giá tính tiền (có làm tròn số nguyên VND). */
    public static function unitTotal(Product $product, ?ProductVariant $variant): float
    {
        return (float) (int) round(self::baseUnit($product) + self::variantAddon($variant));
    }

    /** Đơn giá dòng giỏ / checkout (giữ giá đã lưu nếu không còn bản variant trên đơn lưu). */
    public static function unitTotalForCartLine(Product $product, CartItem $item): float
    {
        if ($item->product_variant_id && $item->productVariant === null) {
            return (float) $item->price;
        }

        return self::unitTotal($product, $item->productVariant);
    }

    /** Nhãn hiển thị variant thống nhất (SKU, màu, chất liệu). */
    public static function variantSummary(?ProductVariant $variant): ?string
    {
        if ($variant === null) {
            return null;
        }

        $segments = [];
        if ($variant->sku_variant) {
            $segments[] = 'SKU: '.$variant->sku_variant;
        }
        if ($variant->color_name) {
            $segments[] = $variant->color_name;
        }
        if ($variant->material_main) {
            $segments[] = $variant->material_main;
        }

        return $segments === [] ? null : implode(' · ', $segments);
    }

}
