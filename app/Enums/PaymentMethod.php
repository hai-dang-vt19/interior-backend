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

    /**
     * Form sửa đơn admin: cùng các lựa chọn với checkout site, thêm các enum còn lại để đơn lịch sử vẫn hiển thị đúng.
     *
     * @return list<self>
     */
    public static function forAdminOrderForm(): array
    {
        $primary = self::forSiteCheckout();
        $seen = [];
        foreach ($primary as $m) {
            $seen[$m->value] = true;
        }
        $tail = [];
        foreach (self::cases() as $case) {
            if (! isset($seen[$case->value])) {
                $tail[] = $case;
            }
        }

        return array_merge($primary, $tail);
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
