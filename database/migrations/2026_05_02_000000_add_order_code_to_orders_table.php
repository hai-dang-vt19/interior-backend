<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_code', 64)->nullable()->unique()->after('id');
        });

        foreach (DB::table('orders')->orderBy('id')->cursor() as $row) {
            $created = Carbon::parse($row->created_at);
            DB::table('orders')->where('id', $row->id)->update([
                'order_code' => sprintf('ORD%d%d', $created->getTimestamp(), $row->id),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['order_code']);
            $table->dropColumn('order_code');
        });
    }
};
