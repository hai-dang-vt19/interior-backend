<?php

namespace App\Enums;

enum PaymentMethod: int
{
    case CASH = 1;
    case COD = 2;
    case BANK_TRANSFER = 3;
    case E_WALLET = 4;

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::COD => 'COD',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::E_WALLET => 'E-Wallet',
        };
    }
} 