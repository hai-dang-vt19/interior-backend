<?php

declare(strict_types=1);

namespace App\Repositories\SiteCart;

use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

class SiteCartRepository implements SiteCartRepositoryInterface
{
    public function __construct(
        private Cart $cartModel,
        private CartItem $cartItemModel,
        private Product $productModel
    ) {}

    public function resolveCart(int $customerId): Cart
    {
        return $this->cartModel->query()->firstOrCreate([
            'customer_id' => $customerId,
        ]);
    }

    public function getCartWithItems(int $customerId): Cart
    {
        $cart = $this->resolveCart($customerId);
        $cart->load(['items.product.images']);

        return $cart;
    }

    public function getCartModelWithItems(int $customerId): Cart
    {
        return $this->getCartWithItems($customerId);
    }

    public function findActiveProductById(int $productId): ?Product
    {
        $product = $this->productModel->query()->find($productId);
        if (!$product) {
            return null;
        }

        return ((int) $product->status->value === ProductStatus::ACTIVE->value) ? $product : null;
    }

    public function findCartItemByCartAndProduct(int $cartId, int $productId): ?CartItem
    {
        return $this->cartItemModel->query()
            ->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();
    }

    public function createCartItem(int $cartId, int $productId, int $quantity, float $price): CartItem
    {
        return $this->cartItemModel->query()->create([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'price' => $price,
        ]);
    }

    public function updateCartItemQuantityAndPrice(CartItem $item, int $quantity, float $price): bool
    {
        return $item->update([
            'quantity' => $quantity,
            'price' => $price,
        ]);
    }

    public function findCartItemByCustomerAndItemId(int $customerId, int $itemId): ?CartItem
    {
        return $this->cartItemModel->query()
            ->where('id', $itemId)
            ->whereHas('cart', function ($query) use ($customerId) {
                $query->where('customer_id', $customerId);
            })
            ->with('product.images')
            ->first();
    }

    public function updateCartItemQuantity(CartItem $item, int $quantity): bool
    {
        return $item->update(['quantity' => $quantity]);
    }

    public function deleteCartItem(CartItem $item): bool
    {
        return (bool) $item->delete();
    }
}
