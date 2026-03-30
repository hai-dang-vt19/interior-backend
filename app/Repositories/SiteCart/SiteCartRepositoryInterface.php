<?php

declare(strict_types=1);

namespace App\Repositories\SiteCart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

interface SiteCartRepositoryInterface
{
    public function resolveCart(int $customerId): Cart;
    public function getCartWithItems(int $customerId): Cart;
    public function getCartModelWithItems(int $customerId): Cart;
    public function findActiveProductById(int $productId): ?Product;
    public function findCartItemByCartAndProduct(int $cartId, int $productId): ?CartItem;
    public function createCartItem(int $cartId, int $productId, int $quantity, float $price): CartItem;
    public function updateCartItemQuantityAndPrice(CartItem $item, int $quantity, float $price): bool;
    public function findCartItemByCustomerAndItemId(int $customerId, int $itemId): ?CartItem;
    public function updateCartItemQuantity(CartItem $item, int $quantity): bool;
    public function deleteCartItem(CartItem $item): bool;
}
