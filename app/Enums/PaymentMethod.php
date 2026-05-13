<?php

namespace App\Enums;

enum PaymentMethod: int
{
    case CASH = 1;
    case COD = 2;
    case BANK_TRANSFER = 3;
    case E_WALLET = 4;
    case VNPAY = 5;

    /** Phương thức được chọn trên trang checkout khách (VNPay thay cho chuyển khoản / ví). */
    public static function forSiteCheckout(): array
    {
        return [self::CASH, self::COD, self::VNPAY];
    }

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Tiền mặt',
            self::COD => 'COD (thu hộ)',
            self::BANK_TRANSFER => 'Chuyển khoản',
            self::E_WALLET => 'Ví điện tử',
            self::VNPAY => 'Thanh toán online (VNPay)',
        };
    }
}
