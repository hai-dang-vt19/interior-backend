<?php

namespace App\Enums;

enum ProductStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 2;
    case OUT_OF_STOCK = 3;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::OUT_OF_STOCK => 'Out of Stock',
        };
    }
} 