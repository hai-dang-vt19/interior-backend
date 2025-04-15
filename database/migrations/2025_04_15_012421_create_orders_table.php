<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained();
            $table->decimal('total_amount', 12, 0)->comment('Tổng tiền (VNĐ)');
            $table->text('shipping_address');
            $table->string('shipping_phone', 20);
            $table->integer('status')->default(OrderStatus::PENDING->value)->comment('1: Pending, 2: Confirmed, 3: Shipping, 4: Delivered, 5: Cancelled');
            $table->integer('payment_method')->default(PaymentMethod::CASH->value)->comment('1: Cash, 2: COD, 3: Bank Transfer, 4: E-Wallet');
            $table->integer('payment_status')->default(PaymentStatus::PENDING->value)->comment('1: Pending, 2: Paid, 3: Failed');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
