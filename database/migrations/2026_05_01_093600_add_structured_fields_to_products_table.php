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
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'sku')) {
                $table->string('sku', 100)->nullable()->unique()->after('name');
            }

            if (! Schema::hasColumn('products', 'description_short')) {
                $table->string('description_short', 500)->nullable()->after('description');
            }

            if (! Schema::hasColumn('products', 'description_long')) {
                $table->text('description_long')->nullable()->after('description_short');
            }

            if (! Schema::hasColumn('products', 'style')) {
                $table->string('style', 100)->nullable()->after('description_long');
            }

            if (! Schema::hasColumn('products', 'space_type')) {
                $table->string('space_type', 150)->nullable()->after('style');
            }

            if (! Schema::hasColumn('products', 'origin')) {
                $table->string('origin', 100)->nullable()->after('space_type');
            }

            if (! Schema::hasColumn('products', 'year_released')) {
                $table->smallInteger('year_released')->nullable()->after('origin');
            }

            if (! Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status');
            }

            if (! Schema::hasColumn('products', 'is_customizable')) {
                $table->boolean('is_customizable')->default(false)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columns = [
                'sku',
                'description_short',
                'description_long',
                'style',
                'space_type',
                'origin',
                'year_released',
                'is_active',
                'is_customizable',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
