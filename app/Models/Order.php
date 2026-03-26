<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'total_amount',
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
    ];

    protected $casts = [
        'total_amount' => 'decimal:0',
        'status' => OrderStatus::class,
        'payment_method' => PaymentMethod::class,
        'payment_status' => PaymentStatus::class,
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
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

    public function formatCreatedAt()
    {
        return $this->created_at->format('d/m/Y');
    }

    public function getTotalDisplay()
    {
        return number_format((float) $this->total_amount, 0, ',', '.') . ' đ';
    }
}
