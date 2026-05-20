<?php

namespace App\Enums;

enum UserRole: int
{
    case ADMIN = 1;
    case STAFF = 2;

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::STAFF => 'Staff',
        };
    }

    /** Route đích sau đăng nhập / truy cập /admin */
    public function defaultLandingRoute(): string
    {
        return match ($this) {
            self::ADMIN => 'admin.dashboard',
            self::STAFF => 'admin.order.index',
        };
    }
} 