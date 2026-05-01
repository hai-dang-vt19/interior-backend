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
        Schema::create('product_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            $table->decimal('length_mm', 8, 2)->nullable();
            $table->decimal('width_mm', 8, 2)->nullable();
            $table->decimal('height_mm', 8, 2)->nullable();
            $table->decimal('weight_kg', 8, 3)->nullable();
            $table->decimal('max_load_kg', 8, 2)->nullable();

            $table->string('spec_key', 100)->nullable();
            $table->string('spec_value', 255)->nullable();
            $table->string('spec_unit', 50)->nullable();
            $table->string('spec_group', 100)->nullable();
            $table->smallInteger('sort_order')->default(0);

            $table->timestamp('created_at')->useCurrent();

            $table->index('product_id', 'idx_specs_product_id');
            $table->index('spec_key', 'idx_specs_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_specs');
    }
};
