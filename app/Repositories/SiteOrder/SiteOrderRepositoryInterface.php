<?php

declare(strict_types=1);

namespace App\Repositories\SiteOrder;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SiteOrderRepositoryInterface
{
    public function resolveCart(int $customerId): Cart;
    public function getCartWithItems(int $customerId): Cart;
    public function getCheckoutItems(Cart $cart, string $selectedItemsCsv): Collection;
    public function createOrderFromCheckout(int $customerId, Cart $cart, Collection $checkoutItems, array $payload): Order;
    public function getOrdersByCustomer(int $customerId, array $filters = []): LengthAwarePaginator;
    public function getOrderDetailByCustomer(int $customerId, int $orderId): Order;
    public function reorderItems(int $customerId, int $orderId): int;
    public function cancelOrderByCustomer(int $customerId, int $orderId): Order;
}
