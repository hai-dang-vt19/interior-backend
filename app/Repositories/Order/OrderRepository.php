<?php

declare(strict_types=1);

namespace App\Repositories\Order;

use App\Enums\PerPage;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Models\OrderReturnRequest;
use App\Models\Product;
use App\Support\CustomerLoyalty;
use App\Support\OrderInventory;
use App\Support\ProductLinePricing;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private Order $model
    ) {}

    public function getOrders(array $params): LengthAwarePaginator
    {
        $orders = $this->model
            ->withTrashed()
            ->with(['customer', 'items.product'])
            ->when(isset($params['order_code']) && trim((string) $params['order_code']) !== '', function (Builder $query) use ($params) {
                $term = trim((string) $params['order_code']);
                $escaped = addcslashes($term, '%_\\');

                return $query->where('order_code', 'like', '%'.$escaped.'%');
            })
            ->when(isset($params['customer_id']) && $params['customer_id'] !== '', function (Builder $query) use ($params) {
                return $query->where('customer_id', (int) $params['customer_id']);
            })
            ->when(isset($params['status']) && $params['status'] !== '', function (Builder $query) use ($params) {
                return $query->where('status', (int) $params['status']);
            })
            ->when(isset($params['payment_status']) && $params['payment_status'] !== '', function (Builder $query) use ($params) {
                return $query->where('payment_status', (int) $params['payment_status']);
            })
            ->when(isset($params['dateFrom']) && $params['dateFrom'] !== '', function (Builder $query) use ($params) {
                $dates = explode(' - ', $params['dateFrom']);
                if (count($dates) === 2) {
                    return $query->whereBetween('created_at', [
                        Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay(),
                        Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay(),
                    ]);
                }
                $date = Carbon::createFromFormat('d/m/Y', $params['dateFrom']);
                return $query->whereDate('created_at', $date->format('Y-m-d'));
            })
            ->when(($params['deleted'] ?? 'active') === 'active', function (Builder $query) {
                return $query->whereNull('deleted_at');
            })
            ->when(($params['deleted'] ?? 'active') === 'trashed', function (Builder $query) {
                return $query->onlyTrashed();
            });

        return $orders->orderByDesc('id')
            ->paginate($params['per_page'] ?? PerPage::PER_PAGE_10->value)
            ->withQueryString();
    }

    public function getCustomers(): Collection
    {
        return Customer::query()->orderBy('full_name')->get();
    }

    public function getProducts(): Collection
    {
        return Product::query()
            ->whereNull('deleted_at')
            ->with([
                'variants' => static function ($query): void {
                    $query->where('is_active', true)
                        ->orderByDesc('is_default')
                        ->orderBy('id');
                },
            ])
            ->orderBy('name')
            ->get();
    }

    public function createOrder(array $params): Order
    {
        return DB::transaction(function () use ($params) {
            $items = $params['order_items'];
            $productIds = collect($items)->pluck('product_id')->unique()->values()->all();
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->with([
                    'variants' => static function ($query): void {
                        $query->where('is_active', true)
                            ->orderByDesc('is_default')
                            ->orderBy('id');
                    },
                ])
                ->get()
                ->keyBy('id');

            $subtotal = $this->sumAdminOrderSubtotal($items, $products);
            $loyaltyApplied = $this->applyLoyaltyForCustomerSubtotal($subtotal, (int) $params['customer_id']);

            $order = $this->model->create([
                'customer_id' => $params['customer_id'],
                'total_amount' => $loyaltyApplied['total_amount'],
                'loyalty_discount_amount' => $loyaltyApplied['loyalty_discount_amount'],
                'loyalty_tier_snapshot' => $loyaltyApplied['loyalty_tier_snapshot'],
                'shipping_address' => $params['shipping_address'],
                'shipping_phone' => $params['shipping_phone'],
                'shipping_provider' => $params['shipping_provider'] ?? null,
                'tracking_number' => $params['tracking_number'] ?? null,
                'shipped_at' => $params['shipped_at'] ?? null,
                'delivered_at' => $params['delivered_at'] ?? null,
                'status' => $params['status'],
                'payment_method' => $params['payment_method'],
                'payment_status' => $params['payment_status'],
                'notes' => $params['notes'] ?? null,
            ]);

            $order->update([
                'order_code' => Order::composeOrderCode((int) $order->id, $order->created_at),
            ]);

            foreach ($items as $item) {
                $product = $products->get((int) $item['product_id']);
                if (! $product) {
                    continue;
                }
                $reqVariant = isset($item['product_variant_id']) && $item['product_variant_id'] !== '' && $item['product_variant_id'] !== null
                    ? (int) $item['product_variant_id']
                    : null;
                $resolved = ProductLinePricing::resolveAdminOrderLine($product, $reqVariant);
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => (int) $item['product_id'],
                    'product_variant_id' => $resolved['variant_id'],
                    'quantity' => (int) $item['quantity'],
                    'price' => $resolved['unit'],
                ]);
            }

            OrderHistory::query()->create([
                'order_id' => $order->id,
                'action' => 'created',
                'note' => 'Tạo đơn hàng mới',
                'changed_by' => auth()->id(),
            ]);

            $this->syncCustomerLoyalty((int) $order->customer_id);

            return $order;
        });
    }

    public function getOrderByID(int $id): Order
    {
        return $this->model->withTrashed()->findOrFail($id);
    }

    public function updateOrderByID(int $id, array $params): bool
    {
        return DB::transaction(function () use ($id, $params) {
            $order = $this->model->withTrashed()->findOrFail($id);
            $previousStatusValue = $order->status->value;
            $oldCustomerId = (int) $order->customer_id;
            $items = $params['order_items'];
            $productIds = collect($items)->pluck('product_id')->unique()->values()->all();
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->with([
                    'variants' => static function ($query): void {
                        $query->where('is_active', true)
                            ->orderByDesc('is_default')
                            ->orderBy('id');
                    },
                ])
                ->get()
                ->keyBy('id');

            $subtotal = $this->sumAdminOrderSubtotal($items, $products);
            $loyaltyApplied = $this->applyLoyaltyForCustomerSubtotal($subtotal, (int) $params['customer_id']);

            $updated = $order->update([
                'customer_id' => $params['customer_id'],
                'total_amount' => $loyaltyApplied['total_amount'],
                'loyalty_discount_amount' => $loyaltyApplied['loyalty_discount_amount'],
                'loyalty_tier_snapshot' => $loyaltyApplied['loyalty_tier_snapshot'],
                'shipping_address' => $params['shipping_address'],
                'shipping_phone' => $params['shipping_phone'],
                'shipping_provider' => $params['shipping_provider'] ?? null,
                'tracking_number' => $params['tracking_number'] ?? null,
                'shipped_at' => $params['shipped_at'] ?? null,
                'delivered_at' => $params['delivered_at'] ?? null,
                'status' => $params['status'],
                'payment_method' => $params['payment_method'],
                'payment_status' => $params['payment_status'],
                'notes' => $params['notes'] ?? null,
            ]);

            if ($updated) {
                $newStatusValue = (int) $params['status'];
                if ($newStatusValue === OrderStatus::CANCELLED->value && $previousStatusValue !== OrderStatus::CANCELLED->value) {
                    OrderInventory::restoreIfNeeded($order);
                }

                OrderItem::query()->where('order_id', $order->id)->delete();
                foreach ($items as $item) {
                    $product = $products->get((int) $item['product_id']);
                    if (! $product) {
                        continue;
                    }
                    $reqVariant = isset($item['product_variant_id']) && $item['product_variant_id'] !== '' && $item['product_variant_id'] !== null
                        ? (int) $item['product_variant_id']
                        : null;
                    $resolved = ProductLinePricing::resolveAdminOrderLine($product, $reqVariant);
                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => (int) $item['product_id'],
                        'product_variant_id' => $resolved['variant_id'],
                        'quantity' => (int) $item['quantity'],
                        'price' => $resolved['unit'],
                    ]);
                }

                OrderHistory::query()->create([
                    'order_id' => $order->id,
                    'action' => 'updated',
                    'note' => 'Cập nhật thông tin đơn hàng',
                    'changed_by' => auth()->id(),
                ]);
            }

            $this->syncCustomerLoyalty($oldCustomerId);
            $this->syncCustomerLoyalty((int) $order->customer_id);

            return $updated;
        });
    }

    public function destroy(int $id): void
    {
        $order = $this->model->findOrFail($id);
        OrderInventory::restoreIfNeeded($order);
        $customerId = (int) $order->customer_id;
        $order->delete();
        OrderHistory::query()->create([
            'order_id' => $order->id,
            'action' => 'cancelled',
            'note' => 'Hủy đơn hàng (xóa mềm)',
            'changed_by' => auth()->id(),
        ]);
        $this->syncCustomerLoyalty($customerId);
    }

    public function restore(int $id): bool
    {
        $order = $this->model->withTrashed()->findOrFail($id);
        $result = (bool) $order->restore();
        if ($result) {
            OrderHistory::query()->create([
                'order_id' => $order->id,
                'action' => 'restored',
                'note' => 'Khôi phục đơn hàng',
                'changed_by' => auth()->id(),
            ]);
            $this->syncCustomerLoyalty((int) $order->customer_id);
        }

        return $result;
    }

    public function forceDelete(int $id): bool
    {
        $order = $this->model->withTrashed()->findOrFail($id);
        OrderInventory::restoreIfNeeded($order);
        $customerId = (int) $order->customer_id;
        $result = (bool) $order->forceDelete();
        if ($result) {
            $this->syncCustomerLoyalty($customerId);
        }

        return $result;
    }

    public function getOrderDetail(int $id): Order
    {
        return $this->model->withTrashed()
            ->with([
                'customer',
                'items.product',
                'items.productVariant',
                'histories' => function ($query) {
                    $query->with('changedBy')->orderByDesc('id');
                },
                'returnRequests' => function ($query) {
                    $query->with('processedBy')->orderByDesc('id');
                },
            ])
            ->findOrFail($id);
    }

    public function addReturnRequest(int $id, array $params, ?int $userId): bool
    {
        $order = $this->model->withTrashed()->findOrFail($id);
        $created = (bool) OrderReturnRequest::query()->create([
            'order_id' => $order->id,
            'type' => $params['type'],
            'reason' => $params['reason'],
            'status' => 'pending',
        ]);

        if ($created) {
            OrderHistory::query()->create([
                'order_id' => $order->id,
                'action' => 'return_request_created',
                'note' => 'Tạo yêu cầu ' . ($params['type'] === 'exchange' ? 'đổi hàng' : 'trả hàng'),
                'changed_by' => $userId,
            ]);
        }

        return $created;
    }

    public function updateReturnRequestStatus(int $id, int $returnId, string $status, ?int $userId): bool
    {
        $order = $this->model->withTrashed()->findOrFail($id);
        $request = OrderReturnRequest::query()
            ->where('order_id', $order->id)
            ->findOrFail($returnId);

        $updated = $request->update([
            'status' => $status,
            'processed_by' => $userId,
            'processed_at' => now(),
        ]);

        if ($updated) {
            OrderHistory::query()->create([
                'order_id' => $order->id,
                'action' => 'return_request_updated',
                'note' => 'Cập nhật trạng thái yêu cầu đổi/trả: ' . $status,
                'changed_by' => $userId,
            ]);
        }

        return $updated;
    }

    public function updateShipping(int $id, array $params, ?int $userId): bool
    {
        $order = $this->model->withTrashed()->findOrFail($id);
        $previousStatusValue = $order->status->value;
        $newStatusValue = (int) $params['status'];
        $updated = $order->update([
            'shipping_provider' => $params['shipping_provider'] ?? null,
            'tracking_number' => $params['tracking_number'] ?? null,
            'status' => $params['status'],
            'shipped_at' => $params['shipped_at'] ?? null,
            'delivered_at' => $params['delivered_at'] ?? null,
        ]);

        if ($updated && $newStatusValue === OrderStatus::CANCELLED->value && $previousStatusValue !== OrderStatus::CANCELLED->value) {
            OrderInventory::restoreIfNeeded($order);
        }

        if ($updated) {
            OrderHistory::query()->create([
                'order_id' => $order->id,
                'action' => 'shipping_updated',
                'note' => $params['note'] ?? 'Cập nhật thông tin giao hàng',
                'changed_by' => $userId,
            ]);
            $this->syncCustomerLoyalty((int) $order->customer_id);
        }

        return $updated;
    }

    /** Đếm đơn trạng thái chờ xác nhận (không tính đơn đã xóa mềm). */
    public function countPendingOrders(): int
    {
        return $this->model->query()
            ->whereNull('deleted_at')
            ->where('status', OrderStatus::PENDING->value)
            ->count();
    }

    /** Danh sách đơn chờ xác nhận mới nhất cho thông báo header admin. */
    public function getPendingOrdersForNotification(int $limit = 40): Collection
    {
        return $this->model->query()
            ->with(['customer:id,full_name'])
            ->whereNull('deleted_at')
            ->where('status', OrderStatus::PENDING->value)
            ->latest('id')
            ->limit($limit)
            ->get(['id', 'order_code', 'customer_id', 'total_amount', 'created_at']);
    }

    /**
     * Tạm tính đơn admin (đơn giá website × SL từng dòng).
     */
    private function sumAdminOrderSubtotal(array $items, Collection $products): float
    {
        $total = 0.0;
        foreach ($items as $item) {
            $product = $products->get((int) $item['product_id']);
            if (! $product) {
                continue;
            }
            $reqVariant = isset($item['product_variant_id']) && $item['product_variant_id'] !== '' && $item['product_variant_id'] !== null
                ? (int) $item['product_variant_id']
                : null;
            $resolved = ProductLinePricing::resolveAdminOrderLine($product, $reqVariant);
            $total += $resolved['unit'] * (int) $item['quantity'];
        }

        return $total;
    }

    /**
     * Trừ chiết khấu hạng giống storefront; lưu snapshot mã hạng lúc lưu đơn.
     *
     * @return array{total_amount: int, loyalty_discount_amount: int, loyalty_tier_snapshot: string}
     */
    private function applyLoyaltyForCustomerSubtotal(float $subtotalVnd, int $customerId): array
    {
        $customer = Customer::query()->find($customerId);
        $tier = (string) ($customer?->loyalty_tier ?? 'standard');
        $loyaltyDiscount = CustomerLoyalty::computeDiscountAmountFromSubtotal($subtotalVnd, $tier);

        return [
            'loyalty_discount_amount' => $loyaltyDiscount,
            'total_amount' => max(0, (int) round($subtotalVnd - $loyaltyDiscount)),
            'loyalty_tier_snapshot' => $tier,
        ];
    }

    // Đồng bộ điểm thưởng và hạng khách hàng theo đơn đã giao + đã thanh toán
    private function syncCustomerLoyalty(int $customerId): void
    {
        if ($customerId <= 0) {
            return;
        }

        $paidDeliveredRevenue = (float) Order::query()
            ->where('customer_id', $customerId)
            ->whereNull('deleted_at')
            ->where('status', OrderStatus::DELIVERED->value)
            ->where('payment_status', PaymentStatus::PAID->value)
            ->sum('total_amount');

        $rewardPoints = CustomerLoyalty::rewardPointsFromPaidDeliveredRevenue($paidDeliveredRevenue);
        $tier = CustomerLoyalty::tierFromRewardPoints($rewardPoints);

        Customer::query()
            ->where('id', $customerId)
            ->update([
                'reward_points' => $rewardPoints,
                'loyalty_tier' => $tier,
            ]);
    }
}
