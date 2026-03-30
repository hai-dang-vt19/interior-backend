<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Repositories\SiteCart\SiteCartRepositoryInterface;

class SiteCartService extends BaseService
{
    public function __construct(
        private SiteCartRepositoryInterface $siteCartRepository
    ) {}

    public function getCartPayloadByCustomer(int $customerId): array
    {
        $cart = $this->siteCartRepository->getCartWithItems($customerId);

        return $this->formatCartPayload($cart);
    }

    public function getCartModelByCustomer(int $customerId): Cart
    {
        return $this->siteCartRepository->getCartModelWithItems($customerId);
    }

    public function addItem(int $customerId, int $productId, int $quantity): array
    {
        $product = $this->siteCartRepository->findActiveProductById($productId);
        if (!$product) {
            return ['ok' => false, 'message' => 'Sản phẩm hiện không khả dụng'];
        }

        $cart = $this->siteCartRepository->resolveCart($customerId);
        $price = (float) ($product->discount_price ?? $product->price);
        $item = $this->siteCartRepository->findCartItemByCartAndProduct((int) $cart->id, (int) $product->id);

        if ($item) {
            $nextQty = (int) $item->quantity + $quantity;
            if ($nextQty > (int) $product->quantity) {
                return ['ok' => false, 'message' => 'Số lượng vượt quá tồn kho'];
            }
            $this->siteCartRepository->updateCartItemQuantityAndPrice($item, $nextQty, $price);
        } else {
            if ($quantity > (int) $product->quantity) {
                return ['ok' => false, 'message' => 'Số lượng vượt quá tồn kho'];
            }
            $this->siteCartRepository->createCartItem((int) $cart->id, (int) $product->id, $quantity, $price);
        }

        $freshCart = $this->siteCartRepository->getCartWithItems($customerId);

        return [
            'ok' => true,
            'message' => 'Đã thêm vào giỏ hàng',
            'data' => $this->formatCartPayload($freshCart),
        ];
    }

    public function updateItemQuantity(int $customerId, int $itemId, int $quantity): array
    {
        $item = $this->siteCartRepository->findCartItemByCustomerAndItemId($customerId, $itemId);
        if (!$item) {
            return ['ok' => false, 'message' => 'Không tìm thấy sản phẩm trong giỏ'];
        }

        if ($quantity > (int) ($item->product->quantity ?? 0)) {
            return ['ok' => false, 'message' => 'Số lượng vượt quá tồn kho'];
        }

        $this->siteCartRepository->updateCartItemQuantity($item, $quantity);
        $freshCart = $this->siteCartRepository->getCartWithItems($customerId);

        return [
            'ok' => true,
            'message' => 'Đã cập nhật giỏ hàng',
            'data' => $this->formatCartPayload($freshCart),
        ];
    }

    public function removeItem(int $customerId, int $itemId): array
    {
        $item = $this->siteCartRepository->findCartItemByCustomerAndItemId($customerId, $itemId);
        if (!$item) {
            return ['ok' => false, 'message' => 'Không tìm thấy sản phẩm trong giỏ'];
        }

        $this->siteCartRepository->deleteCartItem($item);
        $freshCart = $this->siteCartRepository->getCartWithItems($customerId);

        return [
            'ok' => true,
            'message' => 'Đã xóa sản phẩm khỏi giỏ',
            'data' => $this->formatCartPayload($freshCart),
        ];
    }

    /**
     * Chuẩn hóa dữ liệu giỏ hàng trả về cho offcanvas AJAX.
     */
    private function formatCartPayload(Cart $cart): array
    {
        $items = $cart->items
            ->map(function (CartItem $item): array {
                $product = $item->product;
                $primaryStored = optional($product?->images->firstWhere('is_primary', true))->image_url
                    ?: optional($product?->images->first())->image_url;

                return [
                    'id' => (int) $item->id,
                    'product_id' => (int) $item->product_id,
                    'name' => $product?->name,
                    'price' => (float) $item->price,
                    'quantity' => (int) $item->quantity,
                    'stock' => (int) ($product?->quantity ?? 0),
                    'is_out_of_stock' => ((int) ($product?->quantity ?? 0)) < 1,
                    'image_url' => \App\Models\ProductImage::resolvePublicUrl($primaryStored),
                ];
            })
            ->values()
            ->all();

        $total = array_reduce($items, function (float $carry, array $item): float {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0.0);

        return [
            'items' => $items,
            'total' => $total,
            'count' => count($items),
        ];
    }
}
