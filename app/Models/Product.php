<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'discount_price',
        'quantity',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:0',
        'discount_price' => 'decimal:0',
        'status' => ProductStatus::class,
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function productReviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function inventoryHistories()
    {
        return $this->hasMany(InventoryHistory::class);
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