<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->char('email_verification_token_hash', 64)->nullable()->after('email_verified_at');
            $table->timestamp('email_verification_token_expires_at')->nullable()->after('email_verification_token_hash');
        });

        // Khách hàng đã tồn tại coi như đã xác thực để không chặn đăng nhập sau khi triển khai tính năng
        DB::table('customers')->whereNull('email_verified_at')->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'email_verified_at',
                'email_verification_token_hash',
                'email_verification_token_expires_at',
            ]);
        });
    }
};
