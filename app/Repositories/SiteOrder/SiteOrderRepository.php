<?php

declare(strict_types=1);

namespace App\Repositories\SiteOrder;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SiteOrderRepository implements SiteOrderRepositoryInterface
{
    public function __construct(
        private Cart $cartModel,
        private Product $productModel,
        private Order $orderModel,
        private OrderItem $orderItemModel
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
        $cart->load(['items.product']);

        return $cart;
    }

    public function getCheckoutItems(Cart $cart, string $selectedItemsCsv): Collection
    {
        $selectedIds = collect(explode(',', $selectedItemsCsv))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            return collect();
        }

        return $cart->items
            ->whereIn('id', $selectedIds->all())
            ->values();
    }

    public function createOrderFromCheckout(int $customerId, Cart $cart, Collection $checkoutItems, array $payload): Order
    {
        return DB::transaction(function () use ($customerId, $cart, $checkoutItems, $payload) {
            $total = 0;
            foreach ($checkoutItems as $item) {
                $product = $this->productModel->query()->lockForUpdate()->find($item->product_id);
                if (!$product || (int) $product->status->value !== ProductStatus::ACTIVE->value) {
                    throw new \RuntimeException('Sản phẩm không còn khả dụng: '.($item->product?->name ?? 'N/A'));
                }
                if ((int) $item->quantity > (int) $product->quantity) {
                    throw new \RuntimeException('Sản phẩm vượt tồn kho: '.$product->name);
                }
                $total += ((int) $item->quantity) * ((float) $item->price);
            }

            $order = $this->orderModel->query()->create([
                'customer_id' => $customerId,
                'total_amount' => $total,
                'shipping_address' => $payload['shipping_address'],
                'shipping_phone' => $payload['shipping_phone'],
                'status' => OrderStatus::PENDING,
                'payment_method' => (int) $payload['payment_method'],
                'payment_status' => PaymentStatus::PENDING,
                'notes' => $payload['notes'] ?? null,
            ]);

            foreach ($checkoutItems as $item) {
                $this->orderItemModel->query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);

                $product = $this->productModel->query()->find($item->product_id);
                $nextQty = max(0, ((int) $product->quantity) - ((int) $item->quantity));
                $product->quantity = $nextQty;
                if ($nextQty === 0) {
                    $product->status = ProductStatus::OUT_OF_STOCK;
                }
                $product->save();
            }

            $cart->items()->whereIn('id', $checkoutItems->pluck('id')->all())->delete();

            return $order;
        });
    }

    public function getOrdersByCustomer(int $customerId): LengthAwarePaginator
    {
        return $this->orderModel->query()
            ->with('items')
            ->where('customer_id', $customerId)
            ->latest('id')
            ->paginate(10);
    }

    public function getOrderDetailByCustomer(int $customerId, int $orderId): Order
    {
        return $this->orderModel->query()
            ->with(['items.product.images'])
            ->where('customer_id', $customerId)
            ->findOrFail($orderId);
    }
}
