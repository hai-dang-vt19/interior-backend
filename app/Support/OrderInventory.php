<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * Hoàn kho có kiểm soát: chỉ khi đơn đã ghi nhận xuất (stock_deducted_at) và chưa hoàn (stock_restored_at).
 */
final class OrderInventory
{
    /** Hoàn đủ các dòng order_items vào ProductStock theo phiên bản. */
    public static function restoreIfNeeded(Order $order): void
    {
        DB::transaction(static function () use ($order): void {
            /** @var Order|null $locked */
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->first();

            if ($locked === null) {
                return;
            }

            if ($locked->stock_deducted_at === null || $locked->stock_restored_at !== null) {
                return;
            }

            $locked->loadMissing('items');

            foreach ($locked->items as $line) {
                $product = Product::query()->lockForUpdate()->find($line->product_id);

                if ($product === null) {
                    continue;
                }

                ProductStock::incrementForOrderLine($product, $line->product_variant_id, (int) $line->quantity);
            }

            $locked->update(['stock_restored_at' => now()]);
        });
    }

    /** Đánh dấu đơn web đã trừ tồn (sau khi decrement từng dòng thành công). */
    public static function markStockDeducted(Order $order): void
    {
        $order->update(['stock_deducted_at' => now()]);
    }
}
