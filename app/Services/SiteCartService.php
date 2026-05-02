<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Repositories\SiteCart\SiteCartRepositoryInterface;
use App\Support\ProductLinePricing;
use App\Support\ProductStock;

class SiteCartService extends BaseService
{
    public function __construct(
        private SiteCartRepositoryInterface $siteCartRepository
    ) {}

    public function getCartPayloadByCustomer(int $customerId): array
    {
        $cart = $this->siteCartRepository->getCartWithItems($customerId);
        $this->syncCartLinePrices($cart);

        return $this->formatCartPayload($cart);
    }

    public function getCartModelByCustomer(int $customerId): Cart
    {
        $cart = $this->siteCartRepository->getCartModelWithItems($customerId);
        $this->syncCartLinePrices($cart);

        return $cart;
    }

    /**
     * Chuẩn hóa cart_items.price theo Catalog (discount + phụ phí phiên bản — giống trang chi tiết SP).
     */
    public function syncCartLinePrices(Cart $cart): void
    {
        $cart->loadMissing(['items.product', 'items.productVariant']);
        foreach ($cart->items as $item) {
            $fresh = self::resolvedUnitPayableFromCartRow($item);
            if ((int) round((float) $item->price) === (int) round($fresh)) {
                continue;
            }
            $this->siteCartRepository->updateCartItemQuantityAndPrice($item, (int) $item->quantity, $fresh);
            $item->price = $fresh;
        }
    }

    public function addItem(int $customerId, int $productId, int $quantity, ?int $productVariantId = null): array
    {
        $product = $this->siteCartRepository->findActiveProductById($productId);
        if (!$product) {
            return ['ok' => false, 'message' => 'Sản phẩm hiện không khả dụng'];
        }

        $resolved = $this->resolveVariantAndUnitPrice($product, $productVariantId);
        if ($resolved['message'] !== null) {
            return ['ok' => false, 'message' => $resolved['message']];
        }

        $cart = $this->siteCartRepository->resolveCart($customerId);
        $price = $resolved['price'];
        $lineVariantId = $resolved['variant_id'];
        $item = $this->siteCartRepository->findCartItemByCartProductAndVariant((int) $cart->id, (int) $product->id, $lineVariantId);
        $stockAvail = ProductStock::unitsAvailable($product, $lineVariantId);

        if ($item) {
            $nextQty = (int) $item->quantity + $quantity;
            if ($nextQty > $stockAvail) {
                return ['ok' => false, 'message' => 'Số lượng vượt quá tồn kho'];
            }
            $this->siteCartRepository->updateCartItemQuantityAndPrice($item, $nextQty, $price);
        } else {
            if ($quantity > $stockAvail) {
                return ['ok' => false, 'message' => 'Số lượng vượt quá tồn kho'];
            }
            $this->siteCartRepository->createCartItem((int) $cart->id, (int) $product->id, $lineVariantId, $quantity, $price);
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

        $item->loadMissing('product');
        $stockAvail = ProductStock::unitsAvailable($item->product, $item->product_variant_id);
        if ($quantity > $stockAvail) {
            return ['ok' => false, 'message' => 'Số lượng vượt quá tồn kho'];
        }

        $item->loadMissing(['productVariant']);
        $freshUnit = self::resolvedUnitPayableFromCartRow($item);
        $this->siteCartRepository->updateCartItemQuantityAndPrice($item, $quantity, $freshUnit);
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
     * @return array{variant_id: ?int, price: float, message: ?string}
     */
    private function resolveVariantAndUnitPrice(Product $product, ?int $requestedVariantId): array
    {
        $variants = $product->variants;
        if ($variants->isEmpty()) {
            return [
                'variant_id' => null,
                'price' => ProductLinePricing::unitTotal($product, null),
                'message' => null,
            ];
        }

        if ($requestedVariantId !== null) {
            $match = $variants->firstWhere('id', $requestedVariantId);
            if ($match === null) {
                return ['variant_id' => null, 'price' => 0.0, 'message' => 'Phiên bản sản phẩm không hợp lệ'];
            }

            return [
                'variant_id' => (int) $match->id,
                'price' => ProductLinePricing::unitTotal($product, $match),
                'message' => null,
            ];
        }

        $default = $variants->firstWhere('is_default', true) ?? $variants->first();
        if ($default === null) {
            return ['variant_id' => null, 'price' => 0.0, 'message' => 'Sản phẩm chưa có phiên bản mua được'];
        }

        return [
            'variant_id' => (int) $default->id,
            'price' => ProductLinePricing::unitTotal($product, $default),
            'message' => null,
        ];
    }

    /** Đơn giá tính tiền khớp Storefront — giữ đơn lưu nếu variant đã xóa và không có model. */
    public static function resolvedUnitPayableFromCartRow(CartItem $item): float
    {
        if ($item->product === null) {
            return (float) $item->price;
        }

        return ProductLinePricing::unitTotalForCartLine($item->product, $item);
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
                $variant = $item->productVariant;
                $variantLabel = ProductLinePricing::variantSummary($variant);
                $baseUnit = ProductLinePricing::baseUnit($product);
                $variantAddon = ProductLinePricing::variantAddon($variant);
                $unitPayable = (float) $item->price;
                $stockAvail = ProductStock::unitsAvailable($product, $item->product_variant_id);

                return [
                    'id' => (int) $item->id,
                    'product_id' => (int) $item->product_id,
                    'name' => $product?->name,
                    'variant_label' => $variantLabel,
                    'pricing' => [
                        'product_base_unit' => $baseUnit,
                        'variant_addon_unit' => $variantAddon,
                        'unit_payable' => $unitPayable,
                    ],
                    'price' => $unitPayable,
                    'quantity' => (int) $item->quantity,
                    'stock' => $stockAvail,
                    'is_out_of_stock' => $stockAvail < 1,
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
