<?php

declare(strict_types=1);

namespace App\Repositories\Order;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface
{
    public function getOrders(array $params): LengthAwarePaginator;
    public function getCustomers(): Collection;
    public function getProducts(): Collection;
    public function createOrder(array $params): Order;
    public function getOrderByID(int $id): Order;
    public function updateOrderByID(int $id, array $params): bool;
    public function destroy(int $id): void;
    public function restore(int $id): bool;
    public function forceDelete(int $id): bool;
    public function getOrderDetail(int $id): Order;
    public function addReturnRequest(int $id, array $params, ?int $userId): bool;
    public function updateReturnRequestStatus(int $id, int $returnId, string $status, ?int $userId): bool;
    public function updateShipping(int $id, array $params, ?int $userId): bool;
}
