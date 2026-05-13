<?php

namespace App\Enums;

enum PaymentStatus: int
{
    case PENDING = 1;
    case PAID = 2;
    case FAILED = 3;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Chưa thanh toán',
            self::PAID => 'Đã thanh toán',
            self::FAILED => 'Thanh toán thất bại',
        };
    }
}
