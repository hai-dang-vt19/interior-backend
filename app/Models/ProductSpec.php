<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSpec extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'length_mm',
        'width_mm',
        'height_mm',
        'weight_kg',
        'max_load_kg',
        'spec_key',
        'spec_value',
        'spec_unit',
        'spec_group',
        'sort_order',
        'created_at',
    ];

    protected $casts = [
        'length_mm' => 'decimal:2',
        'width_mm' => 'decimal:2',
        'height_mm' => 'decimal:2',
        'weight_kg' => 'decimal:3',
        'max_load_kg' => 'decimal:2',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
    ];

    // Thông tin sản phẩm cha của thông số
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
