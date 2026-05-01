<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            if (! Schema::hasColumn('product_variants', 'sku_variant')) {
                $table->string('sku_variant', 120)->nullable()->unique()->after('product_id');
            }

            if (! Schema::hasColumn('product_variants', 'color_name')) {
                $table->string('color_name', 100)->nullable()->after('sku_variant');
            }

            if (! Schema::hasColumn('product_variants', 'color_hex')) {
                $table->char('color_hex', 7)->nullable()->after('color_name');
            }

            if (! Schema::hasColumn('product_variants', 'material_main')) {
                $table->string('material_main', 150)->nullable()->after('color_hex');
            }

            if (! Schema::hasColumn('product_variants', 'material_sub')) {
                $table->string('material_sub', 150)->nullable()->after('material_main');
            }

            if (! Schema::hasColumn('product_variants', 'finish')) {
                $table->string('finish', 100)->nullable()->after('material_sub');
            }

            if (! Schema::hasColumn('product_variants', 'currency')) {
                $table->char('currency', 3)->default('VND')->after('price');
            }

            if (! Schema::hasColumn('product_variants', 'unit')) {
                $table->string('unit', 50)->default('cai')->after('currency');
            }

            if (! Schema::hasColumn('product_variants', 'qty_per_set')) {
                $table->smallInteger('qty_per_set')->default(1)->after('unit');
            }

            if (! Schema::hasColumn('product_variants', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('qty_per_set');
            }

            if (! Schema::hasColumn('product_variants', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_default');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $columns = [
                'sku_variant',
                'color_name',
                'color_hex',
                'material_main',
                'material_sub',
                'finish',
                'currency',
                'unit',
                'qty_per_set',
                'is_default',
                'is_active',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('product_variants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
