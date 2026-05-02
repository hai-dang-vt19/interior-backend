<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'sku_variant',
        'color_name',
        'color_hex',
        'material_main',
        'material_sub',
        'finish',
        'price',
        'currency',
        'unit',
        'qty_per_set',
        'is_default',
        'is_active',
        'quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'qty_per_set' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Thông tin sản phẩm cha của biến thể
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
