<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'description',
        'description_short',
        'description_long',
        'style',
        'space_type',
        'origin',
        'year_released',
        'price',
        'discount_price',
        'quantity',
        'status',
        'is_active',
        'is_customizable',
    ];

    protected $casts = [
        'price' => 'decimal:0',
        'discount_price' => 'decimal:0',
        'status' => ProductStatus::class,
        'year_released' => 'integer',
        'is_active' => 'boolean',
        'is_customizable' => 'boolean',
    ];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function productReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    public function inventoryHistories(): HasMany
    {
        return $this->hasMany(InventoryHistory::class);
    }

    // Danh sách phiên bản (màu/chất liệu/giá) của sản phẩm
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    // Danh sách thông số kỹ thuật mở rộng của sản phẩm
    public function specs(): HasMany
    {
        return $this->hasMany(ProductSpec::class);
    }

    public function formatCreatedAt()
    {
        return $this->created_at->format('d/m/Y');
    }

    public function formatStatus()
    {
        return match ($this->status) {
            ProductStatus::ACTIVE => '<span class="text-success">' . ProductStatus::ACTIVE->label() . '</span>',
            ProductStatus::INACTIVE => '<span class="text-secondary">' . ProductStatus::INACTIVE->label() . '</span>',
            ProductStatus::OUT_OF_STOCK => '<span class="text-danger">' . ProductStatus::OUT_OF_STOCK->label() . '</span>',
            default => '<span>-</span>',
        };
    }

    public function getPriceDisplay()
    {
        return number_format((float) $this->price, 0, ',', '.') . ' đ';
    }
} 