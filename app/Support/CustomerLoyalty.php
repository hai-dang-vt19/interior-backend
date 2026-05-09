<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Luật điểm thưởng, hạng khách và % giảm giá thành viên (dùng chung storefront + đồng bộ trong OrderRepository).
 */
final class CustomerLoyalty
{
    /** Mỗi 100.000 VND doanh thu tích lũy hợp lệ được tính 1 điểm */
    public const REVENUE_PER_POINT = 100_000;

    /** Ngưỡng điểm tối thiểu theo thứ tự: silver, gold, platinum */
    private const POINTS_SILVER_MIN = 1_000;

    private const POINTS_GOLD_MIN = 3_000;

    private const POINTS_PLATINUM_MIN = 7_000;

    /**
     * Điểm tích lũy từ tổng doanh thu (VND) đơn đã giao + đã thanh toán.
     */
    public static function rewardPointsFromPaidDeliveredRevenue(float $totalVnd): int
    {
        if ($totalVnd <= 0) {
            return 0;
        }

        return (int) floor($totalVnd / self::REVENUE_PER_POINT);
    }

    /**
     * Xác định mã hạng từ điểm tích lũy đã quy đổi.
     */
    public static function tierFromRewardPoints(int $rewardPoints): string
    {
        if ($rewardPoints >= self::POINTS_PLATINUM_MIN) {
            return 'platinum';
        }
        if ($rewardPoints >= self::POINTS_GOLD_MIN) {
            return 'gold';
        }
        if ($rewardPoints >= self::POINTS_SILVER_MIN) {
            return 'silver';
        }

        return 'standard';
    }

    /** Phần trăm chiết khấu trên tạm tính đơn (website) */
    public static function discountPercentForTier(string $tier): int
    {
        return match ($tier) {
            'silver' => 2,
            'gold' => 5,
            'platinum' => 8,
            default => 0,
        };
    }

    /**
     * Số tiền giảm (VND, nguyên) từ tạm tính và hạng hiện tại.
     */
    public static function computeDiscountAmountFromSubtotal(float $subtotalVnd, string $loyaltyTier): int
    {
        if ($subtotalVnd <= 0) {
            return 0;
        }

        $p = self::discountPercentForTier($loyaltyTier);

        return (int) floor($subtotalVnd * $p / 100.0);
    }

    /**
     * Chữ hiển thị ưu đãi theo % (đồng bộ trên CRM / storefront).
     */
    public static function benefitLabel(string $tier): string
    {
        $p = self::discountPercentForTier($tier);

        return $p > 0 ? "Giảm {$p}% trên tạm tính đơn web" : 'Không có giảm thêm';
    }

    /**
     * Các khối mô tả tiêu chí (trang admin / tài liệu nội bộ).
     *
     * @return array<int, array{title: string, items: array<int, string>}>
     */
    public static function adminPolicySections(): array
    {
        $fmt = fn (int $v): string => number_format($v, 0, ',', '.');

        return [
            [
                'title' => 'Tích điểm & thăng hạ',
                'items' => [
                    'Điểm và hạng được tự động cập nhật khi admin thao tác đơn hàng kích hoạt đồng bộ (tạo, sửa, cập nhật vận chuyển, xóa mềm / khôi phục…).',
                    'Chỉ tính các đơn của khách có trạng thái giao là Đã giao và trạng thái thanh toán là Đã thanh toán, đơn chưa bị xóa mềm (theo luật CSDL hiện tại).',
                    sprintf(
                        'Quy đổi điểm: tổng trường total_amount các đơn hợp lệ chia cho %s VND, làm tròn xuống (floor).',
                        $fmt(self::REVENUE_PER_POINT)
                    ),
                    sprintf('Hạng Silver: điểm tích lũy từ %s trở lên.', $fmt(self::POINTS_SILVER_MIN)),
                    sprintf('Hạng Gold: điểm tích lũy từ %s trở lên.', $fmt(self::POINTS_GOLD_MIN)),
                    sprintf('Hạng Platinum: điểm tích lũy từ %s trở lên.', $fmt(self::POINTS_PLATINUM_MIN)),
                    'Dưới ngưỡng Silver là hạng Standard.',
                ],
            ],
            [
                'title' => 'Ưu đãi khi đặt hàng qua website',
                'items' => [
                    'Áp dụng trên tạm tính đơn (tổng thành tiền các dòng trong đơn) tại thời điểm khách đặt, theo hạng thành viên hiện tại trong tài khoản.',
                    sprintf('Standard: %s.', self::benefitLabel('standard')),
                    sprintf('Silver: %s.', self::benefitLabel('silver')),
                    sprintf('Gold: %s.', self::benefitLabel('gold')),
                    sprintf('Platinum: %s.', self::benefitLabel('platinum')),
                    'Đơn tạo chỉnh từ trang quản trị (admin): tổng tiền theo cách nhập và tính sẵn tại chỗ; chỉ luồng khách đặt trên site mới có trừ chiết khấu hạng.',
                ],
            ],
        ];
    }
}
