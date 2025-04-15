<?php

use App\Enums\ProductStatus;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->decimal('price', 12, 0)->comment('Giá sản phẩm (VNĐ)');
            $table->decimal('discount_price', 12, 0)->nullable()->comment('Giá giảm (VNĐ)');
            $table->integer('quantity')->default(0);
            $table->text('image_url')->nullable();
            $table->integer('status')->default(ProductStatus::ACTIVE->value);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
