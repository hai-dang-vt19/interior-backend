<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'stock_deducted_at')) {
                $table->timestamp('stock_deducted_at')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('orders', 'stock_restored_at')) {
                $table->timestamp('stock_restored_at')->nullable()->after('stock_deducted_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'stock_restored_at')) {
                $table->dropColumn('stock_restored_at');
            }
            if (Schema::hasColumn('orders', 'stock_deducted_at')) {
                $table->dropColumn('stock_deducted_at');
            }
        });
    }
};
