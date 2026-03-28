<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_url',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Chuẩn hóa URL hiển thị: đường dẫn trong disk public hoặc URL đầy đủ (dữ liệu cũ).
     */
    public static function resolvePublicUrl(?string $stored): ?string
    {
        if ($stored === null || $stored === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $stored)) {
            return $stored;
        }

        return Storage::disk('public')->url(ltrim($stored, '/'));
    }
} 