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
    public function getOrdersByCustomer(int $customerId): LengthAwarePaginator;
    public function getOrderDetailByCustomer(int $customerId, int $orderId): Order;
}
