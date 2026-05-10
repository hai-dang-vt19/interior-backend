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
            self::PENDING => 'Chờ xác nhận',
            self::CONFIRMED => 'Đã xác nhận',
            self::SHIPPING => 'Đang giao hàng',
            self::DELIVERED => 'Đã giao hàng',
            self::CANCELLED => 'Đã hủy',
        };
    }
} 