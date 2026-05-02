<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

/**
 * Quản lý tồn kho storefront: không có variant thì dùng products.quantity,
 * có variant thì dùng từng product_variants.quantity (products.quantity là tổng đồng bộ).
 */
final class ProductStock
{
    public static function usesVariantStock(Product $product): bool
    {
        return $product->variants()->exists();
    }

    /** Tồn hiển thị trên listing (ưu tiên mặc định có variant). */
    public static function displayUnits(Product $product, ?ProductVariant $defaultVariant): int
    {
        if (self::usesVariantStock($product)) {
            if ($defaultVariant) {
                return max(0, (int) $defaultVariant->quantity);
            }

            return max(0, (int) $product->variants()->sum('quantity'));
        }

        return max(0, (int) $product->quantity);
    }

    /** Tồn cho một phiên bản cụ thể (khi không có variant: tồn SP). */
    public static function unitsAvailable(Product $product, ?int $productVariantId): int
    {
        if (! self::usesVariantStock($product)) {
            return max(0, (int) $product->quantity);
        }

        if ($productVariantId === null) {
            return max(0, (int) $product->variants()->sum('quantity'));
        }

        $row = ProductVariant::query()
            ->where('product_id', $product->id)
            ->where('id', $productVariantId)
            ->first();

        return $row ? max(0, (int) $row->quantity) : 0;
    }

    /** Cập nhật products.quantity = SUM(variants) và có thể gắn hết hàng. */
    public static function refreshProductAggregateFromVariants(int $productId): void
    {
        if (! ProductVariant::query()->where('product_id', $productId)->exists()) {
            return;
        }

        $sum = (int) ProductVariant::query()->where('product_id', $productId)->sum('quantity');
        DB::transaction(function () use ($productId, $sum): void {
            $product = Product::query()->lockForUpdate()->find($productId);
            if ($product === null) {
                return;
            }

            $product->quantity = max(0, $sum);

            $product->status = self::adjustedProductStatusAfterVariantSum($product, $sum);

            $product->save();
        });
    }

    /** Nhập lại kho khi huỷ đơn (đối xứng với decrement — cùng cách resolve variant id). */
    public static function incrementForOrderLine(Product $product, ?int $productVariantId, int $returnQty): void
    {
        if ($returnQty <= 0) {
            return;
        }

        DB::transaction(function () use ($product, $productVariantId, $returnQty): void {
            if (! self::usesVariantStock($product)) {
                $locked = Product::query()->where('id', $product->id)->lockForUpdate()->first();
                if ($locked !== null) {
                    $next = (int) $locked->quantity + $returnQty;
                    $locked->update([
                        'quantity' => $next,
                        'status' => self::adjustedPlainProductStockStatusFromQuantity($locked, $next),
                    ]);
                }

                return;
            }

            $effectiveVariantId = $productVariantId;
            if ($effectiveVariantId === null) {
                $fallback = ProductVariant::query()
                    ->where('product_id', $product->id)
                    ->orderByDesc('is_default')
                    ->orderBy('id')
                    ->first();
                $effectiveVariantId = $fallback?->id;
            }

            if ($effectiveVariantId !== null) {
                $variant = ProductVariant::query()
                    ->where('product_id', $product->id)
                    ->where('id', $effectiveVariantId)
                    ->lockForUpdate()
                    ->first();

                if ($variant !== null) {
                    $next = (int) $variant->quantity + $returnQty;
                    $variant->update(['quantity' => $next]);
                }
            }

            self::refreshProductAggregateFromVariants((int) $product->id);
        });
    }

    private static function adjustedProductStatusAfterVariantSum(Product $product, int $sum): ProductStatus
    {
        if ($sum <= 0 && (int) $product->status->value === ProductStatus::ACTIVE->value) {
            return ProductStatus::OUT_OF_STOCK;
        }
        if ($sum > 0 && (int) $product->status->value === ProductStatus::OUT_OF_STOCK->value) {
            return ProductStatus::ACTIVE;
        }

        return $product->status;
    }

    private static function adjustedPlainProductStockStatusFromQuantity(Product $product, int $nextQty): ProductStatus
    {
        if ($nextQty <= 0 && (int) $product->status->value === ProductStatus::ACTIVE->value) {
            return ProductStatus::OUT_OF_STOCK;
        }
        if ($nextQty > 0 && (int) $product->status->value === ProductStatus::OUT_OF_STOCK->value) {
            return ProductStatus::ACTIVE;
        }

        return $product->status;
    }

    /** Xuất kho khi khách đặt đơn (theo phiên bản nếu có). */
    public static function decrementForOrderLine(Product $product, ?int $productVariantId, int $soldQty): void
    {
        if ($soldQty <= 0) {
            return;
        }

        DB::transaction(function () use ($product, $productVariantId, $soldQty): void {
            if (! self::usesVariantStock($product)) {
                $locked = Product::query()->where('id', $product->id)->lockForUpdate()->first();
                if ($locked) {
                    $next = max(0, (int) $locked->quantity - $soldQty);
                    $locked->update([
                        'quantity' => $next,
                        'status' => $next <= 0 && (int) $locked->status->value === ProductStatus::ACTIVE->value
                            ? ProductStatus::OUT_OF_STOCK
                            : $locked->status,
                    ]);
                }

                return;
            }

            $effectiveVariantId = $productVariantId;
            if ($effectiveVariantId === null) {
                $fallback = ProductVariant::query()
                    ->where('product_id', $product->id)
                    ->orderByDesc('is_default')
                    ->orderBy('id')
                    ->first();
                $effectiveVariantId = $fallback?->id;
            }

            if ($effectiveVariantId !== null) {
                $variant = ProductVariant::query()
                    ->where('product_id', $product->id)
                    ->where('id', $effectiveVariantId)
                    ->lockForUpdate()
                    ->first();

                if ($variant) {
                    $next = max(0, (int) $variant->quantity - $soldQty);
                    $variant->update(['quantity' => $next]);
                }
            }

            self::refreshProductAggregateFromVariants((int) $product->id);
        });
    }
}
