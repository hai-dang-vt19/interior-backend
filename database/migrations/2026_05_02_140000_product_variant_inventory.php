<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            if (! Schema::hasColumn('product_variants', 'quantity')) {
                $table->unsignedInteger('quantity')->default(0)->after('price');
            }
        });

        Schema::table('inventory_history', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_history', 'product_variant_id')) {
                $table->foreignId('product_variant_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('product_variants')
                    ->nullOnDelete();
            }
        });

        $this->backfillVariantQuantitiesFromProducts();
        $this->resyncProductsQuantityFromVariants();
    }

    private function backfillVariantQuantitiesFromProducts(): void
    {
        if (! Schema::hasColumn('product_variants', 'quantity')) {
            return;
        }

        $productIdsWithVariants = DB::table('product_variants')->distinct()->pluck('product_id');
        foreach ($productIdsWithVariants as $pid) {
            $pid = (int) $pid;
            /** @var Product|null $product */
            $product = Product::with(['variants' => fn ($q) => $q->orderByDesc('is_default')->orderBy('id')])->find($pid);
            if (! $product || $product->variants->isEmpty()) {
                continue;
            }
            $target = $product->variants->firstWhere('is_default', true) ?? $product->variants->first();
            if ($target) {
                DB::table('product_variants')
                    ->where('id', $target->id)
                    ->update(['quantity' => max(0, (int) $product->quantity)]);
            }
        }
    }

    private function resyncProductsQuantityFromVariants(): void
    {
        if (! Schema::hasColumn('product_variants', 'quantity')) {
            return;
        }

        foreach (Product::has('variants')->pluck('id') as $id) {
            $sum = (int) DB::table('product_variants')->where('product_id', $id)->sum('quantity');
            DB::table('products')->where('id', $id)->update(['quantity' => $sum]);
        }
    }

    public function down(): void
    {
        Schema::table('inventory_history', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_history', 'product_variant_id')) {
                $table->dropForeign(['product_variant_id']);
                $table->dropColumn('product_variant_id');
            }
        });

        Schema::table('product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_variants', 'quantity')) {
                $table->dropColumn('quantity');
            }
        });
    }
};
