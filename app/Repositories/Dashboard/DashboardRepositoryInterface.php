<?php

declare(strict_types=1);

namespace App\Repositories\Dashboard;

interface DashboardRepositoryInterface
{
    public function getRevenueByDateRange(string $from, string $to): array;
    public function getTopSellingProducts(string $from, string $to, int $limit = 10): array;
    public function getTopCustomers(string $from, string $to, int $limit = 10): array;
    public function getSummary(string $from, string $to): array;
}
