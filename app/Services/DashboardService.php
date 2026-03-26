<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Dashboard\DashboardRepositoryInterface;
use Carbon\Carbon;

class DashboardService extends BaseService
{
    public function __construct(
        private DashboardRepositoryInterface $dashboardRepository
    ) {}

    // Tổng hợp dữ liệu dashboard theo bộ lọc ngày
    public function getDashboardReport(array $params): array
    {
        [$from, $to] = $this->resolveDateRange($params);

        return [
            'filters' => [
                'from' => $from,
                'to' => $to,
            ],
            'summary' => $this->dashboardRepository->getSummary($from, $to),
            'revenueRows' => $this->dashboardRepository->getRevenueByDateRange($from, $to),
            'topProducts' => $this->dashboardRepository->getTopSellingProducts($from, $to),
            'topCustomers' => $this->dashboardRepository->getTopCustomers($from, $to),
        ];
    }

    public function getRevenueRows(array $params): array
    {
        [$from, $to] = $this->resolveDateRange($params);

        return $this->dashboardRepository->getRevenueByDateRange($from, $to);
    }

    private function resolveDateRange(array $params): array
    {
        if (!empty($params['dateFrom'])) {
            $dates = explode(' - ', $params['dateFrom']);
            if (count($dates) === 2) {
                return [
                    Carbon::createFromFormat('d/m/Y', trim($dates[0]))->format('Y-m-d'),
                    Carbon::createFromFormat('d/m/Y', trim($dates[1]))->format('Y-m-d'),
                ];
            }
        }

        return [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')];
    }
}
