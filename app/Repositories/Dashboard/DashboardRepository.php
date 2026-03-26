<?php

declare(strict_types=1);

namespace App\Repositories\Dashboard;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getRevenueByDateRange(string $from, string $to): array
    {
        return DB::table('orders')
            ->selectRaw("DATE(created_at) as date, COUNT(*) as orders_count, COALESCE(SUM(total_amount), 0) as revenue")
            ->whereNull('deleted_at')
            ->where('payment_status', PaymentStatus::PAID->value)
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->groupByRaw("DATE(created_at)")
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'orders_count' => (int) $row->orders_count,
                'revenue' => (float) $row->revenue,
            ])
            ->all();
    }

    public function getTopSellingProducts(string $from, string $to, int $limit = 10): array
    {
        return DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'p.id', '=', 'oi.product_id')
            ->selectRaw('p.id, p.name, SUM(oi.quantity) as sold_qty, COALESCE(SUM(oi.quantity * oi.price), 0) as sold_revenue')
            ->whereNull('o.deleted_at')
            ->whereBetween('o.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('sold_qty')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'name' => $row->name,
                'sold_qty' => (int) $row->sold_qty,
                'sold_revenue' => (float) $row->sold_revenue,
            ])
            ->all();
    }

    public function getTopCustomers(string $from, string $to, int $limit = 10): array
    {
        return DB::table('orders as o')
            ->join('customers as c', 'c.id', '=', 'o.customer_id')
            ->selectRaw('c.id, c.full_name, c.phone, COUNT(o.id) as orders_count, COALESCE(SUM(o.total_amount), 0) as total_spent')
            ->whereNull('o.deleted_at')
            ->whereBetween('o.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->groupBy('c.id', 'c.full_name', 'c.phone')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'full_name' => $row->full_name,
                'phone' => $row->phone,
                'orders_count' => (int) $row->orders_count,
                'total_spent' => (float) $row->total_spent,
            ])
            ->all();
    }

    public function getSummary(string $from, string $to): array
    {
        $summary = DB::table('orders')
            ->selectRaw('COUNT(*) as orders_total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as delivered_total', [OrderStatus::DELIVERED->value])
            ->selectRaw('SUM(CASE WHEN payment_status = ? THEN total_amount ELSE 0 END) as paid_revenue', [PaymentStatus::PAID->value])
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->first();

        return [
            'orders_total' => (int) ($summary->orders_total ?? 0),
            'delivered_total' => (int) ($summary->delivered_total ?? 0),
            'paid_revenue' => (float) ($summary->paid_revenue ?? 0),
        ];
    }
}
