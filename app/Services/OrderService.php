<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Support\CustomerOrderNotifier;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OrderService extends BaseService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {}

    public function getOrders(array $params): LengthAwarePaginator
    {
        return $this->orderRepository->getOrders($params);
    }

    public function getCustomers(): Collection
    {
        return $this->orderRepository->getCustomers();
    }

    public function getProducts(): Collection
    {
        return $this->orderRepository->getProducts();
    }

    public function createOrder(array $params): Order
    {
        $order = $this->orderRepository->createOrder($params);
        CustomerOrderNotifier::sendOrderUpdatedEmail($order, CustomerOrderNotifier::CONTEXT_ADMIN_CREATED);

        return $order;
    }

    public function getOrderByID(int $id): Order
    {
        return $this->orderRepository->getOrderByID($id);
    }

    public function updateOrderByID(int $id, array $params): bool
    {
        $ok = $this->orderRepository->updateOrderByID($id, $params);

        if ($ok) {
            $order = $this->orderRepository->getOrderByID($id);
            CustomerOrderNotifier::sendOrderUpdatedEmail($order, CustomerOrderNotifier::CONTEXT_ADMIN_FULL);
        }

        return $ok;
    }

    public function destroy(int $id): void
    {
        $this->orderRepository->destroy($id);
    }

    public function restore(int $id): bool
    {
        return $this->orderRepository->restore($id);
    }

    public function forceDelete(int $id): bool
    {
        return $this->orderRepository->forceDelete($id);
    }

    public function getOrderDetail(int $id): Order
    {
        return $this->orderRepository->getOrderDetail($id);
    }

    public function addReturnRequest(int $id, array $params, ?int $userId): bool
    {
        return $this->orderRepository->addReturnRequest($id, $params, $userId);
    }

    public function updateReturnRequestStatus(int $id, int $returnId, string $status, ?int $userId): bool
    {
        return $this->orderRepository->updateReturnRequestStatus($id, $returnId, $status, $userId);
    }

    public function updateShipping(int $id, array $params, ?int $userId): bool
    {
        $ok = $this->orderRepository->updateShipping($id, $params, $userId);

        if ($ok) {
            $order = $this->orderRepository->getOrderByID($id);
            CustomerOrderNotifier::sendOrderUpdatedEmail($order, CustomerOrderNotifier::CONTEXT_ADMIN_SHIPPING);
        }

        return $ok;
    }

    /**
     * Dữ liệu thông báo đơn chờ xác nhận (badge + modal admin).
     *
     * @return array{count: int, list_limit: int, orders: array<int, array{id: int, order_code: string|null, customer_name: string, total_display: string, created_at: string|null, url: string}>}
     */
    public function getPendingOrderNotificationsPayload(int $listLimit = 40): array
    {
        $count = $this->orderRepository->countPendingOrders();
        $orders = $this->orderRepository->getPendingOrdersForNotification($listLimit);

        return [
            'count' => $count,
            'list_limit' => $listLimit,
            'orders' => $orders
                ->map(static function (Order $order): array {
                    return [
                        'id' => (int) $order->id,
                        'order_code' => $order->order_code,
                        'customer_name' => (string) ($order->customer?->full_name ?? '—'),
                        'total_display' => $order->getTotalDisplay(),
                        'created_at' => $order->created_at?->format('d/m/Y H:i'),
                        'url' => $order->order_code
                            ? route('admin.order.index', ['order_code' => $order->order_code])
                            : route('admin.order.show', ['id' => $order->id]),
                    ];
                })
                ->values()
                ->all(),
        ];
    }
}
