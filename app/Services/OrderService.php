<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Repositories\Order\OrderRepositoryInterface;
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
        return $this->orderRepository->createOrder($params);
    }

    public function getOrderByID(int $id): Order
    {
        return $this->orderRepository->getOrderByID($id);
    }

    public function updateOrderByID(int $id, array $params): bool
    {
        return $this->orderRepository->updateOrderByID($id, $params);
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
        return $this->orderRepository->updateShipping($id, $params, $userId);
    }
}
