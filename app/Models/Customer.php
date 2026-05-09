<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Support\CustomerLoyalty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'email',
        'password',
        'full_name',
        'phone',
        'loyalty_tier',
        'reward_points',
        'deleted_at',
        'email_verified_at',
        'email_verification_token_hash',
        'email_verification_token_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token_hash',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_verification_token_expires_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Kiểm tra khách đã xác nhận email đăng ký hay chưa
     */
    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function productReviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function contactLogs()
    {
        return $this->hasMany(CustomerContactLog::class);
    }

    public function formatCreatedAt()
    {
        return $this->created_at->format('d/m/Y');
    }

    public function formatStatus()
    {
        return $this->deleted_at 
            ? '<span class="text-danger">' . CustomerStatus::INACTIVE->label() . '</span>' 
            : '<span class="text-success">' . CustomerStatus::ACTIVE->label() . '</span>';
    }

    public function getIDStatus()
    {
        return $this->deleted_at ? CustomerStatus::INACTIVE->value : CustomerStatus::ACTIVE->value;
    }

    public function formatLoyaltyTier()
    {
        return match ($this->loyalty_tier) {
            'silver' => 'Silver',
            'gold' => 'Gold',
            'platinum' => 'Platinum',
            default => 'Standard',
        };
    }

    public function getLoyaltyBenefit()
    {
        return CustomerLoyalty::benefitLabel((string) $this->loyalty_tier);
    }
} 