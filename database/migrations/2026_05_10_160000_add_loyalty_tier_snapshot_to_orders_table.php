<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'loyalty_tier_snapshot')) {
                $table->string('loyalty_tier_snapshot', 20)->nullable()->after('loyalty_discount_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'loyalty_tier_snapshot')) {
                $table->dropColumn('loyalty_tier_snapshot');
            }
        });
    }
};
