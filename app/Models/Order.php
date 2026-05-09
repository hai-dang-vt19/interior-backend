<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_code',
        'customer_id',
        'total_amount',
        'loyalty_discount_amount',
        'shipping_address',
        'shipping_phone',
        'shipping_provider',
        'tracking_number',
        'shipped_at',
        'delivered_at',
        'status',
        'payment_method',
        'payment_status',
        'notes',
        'stock_deducted_at',
        'stock_restored_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:0',
        'loyalty_discount_amount' => 'integer',
        'status' => OrderStatus::class,
        'payment_method' => PaymentMethod::class,
        'payment_status' => PaymentStatus::class,
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'stock_deducted_at' => 'datetime',
        'stock_restored_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function histories()
    {
        return $this->hasMany(OrderHistory::class);
    }

    public function returnRequests()
    {
        return $this->hasMany(OrderReturnRequest::class);
    }

    /**
     * Mã hiển thị: ORD + unix timestamp + id (liền nhau, không dấu phân tách).
     */
    public static function composeOrderCode(int $id, CarbonInterface $createdAt): string
    {
        return sprintf('ORD%d%d', $createdAt->getTimestamp(), $id);
    }

    public function formatCreatedAt()
    {
        return $this->created_at->format('d/m/Y');
    }

    public function getTotalDisplay()
    {
        return number_format((float) $this->total_amount, 0, ',', '.') . ' đ';
    }
}
