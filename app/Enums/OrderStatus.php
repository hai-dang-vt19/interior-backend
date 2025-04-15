<?php

namespace App\Enums;

enum OrderStatus: int
{
    case PENDING = 1;
    case CONFIRMED = 2;
    case SHIPPING = 3;
    case DELIVERED = 4;
    case CANCELLED = 5;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::SHIPPING => 'Shipping',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }
} 