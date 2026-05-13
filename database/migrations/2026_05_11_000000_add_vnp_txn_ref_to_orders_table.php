<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MãTxnRef đối chiếu với VNPay — mỗi lần redirect thanh toán gán một mã duy nhất.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'vnp_txn_ref')) {
                $table->string('vnp_txn_ref', 120)->nullable()->unique()->after('order_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'vnp_txn_ref')) {
                $table->dropUnique(['vnp_txn_ref']);
                $table->dropColumn('vnp_txn_ref');
            }
        });
    }
};
