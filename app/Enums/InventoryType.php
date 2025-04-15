<?php

namespace App\Enums;

enum InventoryType: int
{
    case IMPORT = 1;
    case EXPORT = 2;
    case ADJUSTMENT = 3;

    public function label(): string
    {
        return match ($this) {
            self::IMPORT => 'Import',
            self::EXPORT => 'Export',
            self::ADJUSTMENT => 'Adjustment',
        };
    }
} 