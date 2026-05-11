<?php

declare(strict_types=1);

namespace App\Repositories\SiteOrder;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\CustomerLoyalty;
use App\Support\OrderInventory;
use App\Support\ProductLinePricing;
use App\Support\ProductStock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SiteOrderRepository implements SiteOrderRepositoryInterface
{
    public function __construct(
        private Cart $cartModel,
        private Product $productModel,
        private Order $orderModel,
        private OrderItem $orderItemModel,
        private Customer $customerModel
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
        $cart->load(['items.product.images', 'items.productVariant']);

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
            $customer = $this->customerModel->query()->whereKey($customerId)->lockForUpdate()->first();
            if ($customer === null) {
                throw new \RuntimeException('Không tìm thấy tài khoản khách hàng.');
            }

            $subtotal = 0.0;
            foreach ($checkoutItems as $item) {
                $product = $this->productModel->query()->lockForUpdate()->find($item->product_id);
                if (! $product || (int) $product->status->value !== ProductStatus::ACTIVE->value) {
                    throw new \RuntimeException('Sản phẩm không còn khả dụng: '.($item->product?->name ?? 'N/A'));
                }
                $avail = ProductStock::unitsAvailable($product, $item->product_variant_id);
                if ((int) $item->quantity > $avail) {
                    throw new \RuntimeException('Sản phẩm vượt tồn kho: '.$product->name);
                }
                $unit = ProductLinePricing::unitTotalForCartLine($product, $item);
                $subtotal += ((int) $item->quantity) * $unit;
            }

            $loyaltyDiscount = CustomerLoyalty::computeDiscountAmountFromSubtotal($subtotal, (string) $customer->loyalty_tier);
            $total = max(0, (int) round($subtotal - $loyaltyDiscount));

            $paymentMethod = PaymentMethod::tryFrom((int) $payload['payment_method']);
            $txnRef = ($paymentMethod === PaymentMethod::VNPAY) ? $this->allocateUniqueVnpTxnRef() : null;

            $order = $this->orderModel->query()->create([
                'customer_id' => $customerId,
                'loyalty_discount_amount' => $loyaltyDiscount,
                'loyalty_tier_snapshot' => (string) $customer->loyalty_tier,
                'total_amount' => $total,
                'shipping_address' => $payload['shipping_address'],
                'shipping_phone' => $payload['shipping_phone'],
                'status' => OrderStatus::PENDING,
                'payment_method' => (int) $payload['payment_method'],
                'payment_status' => PaymentStatus::PENDING,
                'notes' => $payload['notes'] ?? null,
                'vnp_txn_ref' => $txnRef,
            ]);

            $order->update([
                'order_code' => Order::composeOrderCode((int) $order->id, $order->created_at),
            ]);

            foreach ($checkoutItems as $item) {
                $productLine = $this->productModel->query()->lockForUpdate()->find($item->product_id);
                if (! $productLine || (int) $productLine->status->value !== ProductStatus::ACTIVE->value) {
                    throw new \RuntimeException('Sản phẩm không còn khả dụng: '.($item->product?->name ?? 'N/A'));
                }

                $this->orderItemModel->query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'price' => ProductLinePricing::unitTotalForCartLine($productLine, $item),
                ]);

                ProductStock::decrementForOrderLine($productLine, $item->product_variant_id, (int) $item->quantity);
            }

            OrderInventory::markStockDeducted($order);

            $cart->items()->whereIn('id', $checkoutItems->pluck('id')->all())->delete();

            return $order;
        });
    }

    public function cancelOrderByCustomer(int $customerId, int $orderId): Order
    {
        return DB::transaction(function () use ($customerId, $orderId): Order {
            /** @var Order $order */
            $order = $this->orderModel->query()
                ->where('customer_id', $customerId)
                ->whereKey($orderId)
                ->lockForUpdate()
                ->firstOrFail();

            $cancelableStatuses = [OrderStatus::PENDING, OrderStatus::CONFIRMED];
            if (! in_array($order->status, $cancelableStatuses, true)) {
                throw new \RuntimeException('Chỉ có thể huỷ đơn khi đang chờ xác nhận hoặc đã xác nhận và chưa giao.');
            }

            OrderInventory::restoreIfNeeded($order);

            $order->update([
                'status' => OrderStatus::CANCELLED,
            ]);

            OrderHistory::query()->create([
                'order_id' => $order->id,
                'action' => 'customer_cancelled',
                'note' => 'Khách hàng huỷ đơn',
                'changed_by' => null,
            ]);

            return $order->fresh();
        });
    }

    public function getOrdersByCustomer(int $customerId, array $filters = []): LengthAwarePaginator
    {
        $status = isset($filters['status']) ? (int) $filters['status'] : 0;

        return $this->orderModel->query()
            ->with('items')
            ->where('customer_id', $customerId)
            ->when($status > 0, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest('id')
            ->paginate(10)
            ->appends($filters);
    }

    public function getOrderDetailByCustomer(int $customerId, int $orderId): Order
    {
        return $this->orderModel->query()
            ->with(['items.product.images', 'items.productVariant'])
            ->where('customer_id', $customerId)
            ->findOrFail($orderId);
    }

    public function reorderItems(int $customerId, int $orderId): int
    {
        return DB::transaction(function () use ($customerId, $orderId) {
            $order = $this->getOrderDetailByCustomer($customerId, $orderId);
            $cart = $this->resolveCart($customerId);
            $addedCount = 0;

            foreach ($order->items as $item) {
                $product = $this->productModel->query()
                    ->with(['variants' => function ($query) {
                        $query->where('is_active', true)
                            ->orderByDesc('is_default')
                            ->orderBy('id');
                    }])
                    ->find($item->product_id);
                if (! $product || (int) ($product->status?->value ?? 0) !== ProductStatus::ACTIVE->value) {
                    continue;
                }

                $variant = null;
                if ($item->product_variant_id) {
                    $variant = $product->variants->firstWhere('id', (int) $item->product_variant_id);
                }
                if ($variant === null && $product->variants->isNotEmpty()) {
                    $variant = $product->variants->firstWhere('is_default', true) ?? $product->variants->first();
                }

                $variantKey = $variant?->id;
                $avail = ProductStock::unitsAvailable($product, $variantKey);
                if ($avail <= 0) {
                    continue;
                }

                $desiredQty = min((int) $item->quantity, $avail);
                if ($desiredQty <= 0) {
                    continue;
                }

                $lineUnit = ProductLinePricing::unitTotal($product, $variant);

                $existingQuery = CartItem::query()
                    ->where('cart_id', $cart->id)
                    ->where('product_id', $product->id);
                if ($variantKey !== null) {
                    $existingQuery->where('product_variant_id', $variantKey);
                } else {
                    $existingQuery->whereNull('product_variant_id');
                }
                /** @var CartItem|null $existing */
                $existing = $existingQuery->first();

                if ($existing) {
                    $nextQty = min($avail, (int) $existing->quantity + $desiredQty);
                    $existing->update([
                        'quantity' => $nextQty,
                        'price' => $lineUnit,
                    ]);
                } else {
                    CartItem::query()->create([
                        'cart_id' => $cart->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variantKey,
                        'quantity' => $desiredQty,
                        'price' => $lineUnit,
                    ]);
                }

                $addedCount++;
            }

            return $addedCount;
        });
    }

    public function lockOrderByVnpTxnRef(string $txnRef): ?Order
    {
        $trimmed = trim($txnRef);
        if ($trimmed === '') {
            return null;
        }

        /** @var Order|null $row */
        $row = $this->orderModel->query()
            ->where('vnp_txn_ref', $trimmed)
            ->lockForUpdate()
            ->first();

        return $row;
    }

    public function finalizeVnpayOnlinePayment(Order $lockedOrder, int $vnpScaledAmount, bool $paidByGateway, ?string $vnpTransactionNo): array
    {
        $method = $lockedOrder->payment_method;
        if ($method !== PaymentMethod::VNPAY) {
            return ['rsp_code' => '01', 'message' => 'Order not found', 'became_paid' => false];
        }

        $expectedScaled = (int) round((float) $lockedOrder->total_amount * 100);
        if ($vnpScaledAmount !== $expectedScaled) {
            return ['rsp_code' => '04', 'message' => 'invalid amount', 'became_paid' => false];
        }

        $wasPaid = $lockedOrder->payment_status === PaymentStatus::PAID;

        if ($paidByGateway) {
            if ($wasPaid) {
                return ['rsp_code' => '00', 'message' => 'Confirm Success', 'became_paid' => false];
            }

            $lockedOrder->update([
                'payment_status' => PaymentStatus::PAID,
            ]);

            OrderHistory::query()->create([
                'order_id' => $lockedOrder->id,
                'action' => 'vnpay_payment',
                'note' => 'VNPay: thanh toán thành công'.($vnpTransactionNo !== null && $vnpTransactionNo !== '' ? (' (GD: '.$vnpTransactionNo.')') : ''),
                'changed_by' => null,
            ]);

            return ['rsp_code' => '00', 'message' => 'Confirm Success', 'became_paid' => true];
        }

        if ($lockedOrder->payment_status === PaymentStatus::PENDING) {
            $lockedOrder->update([
                'payment_status' => PaymentStatus::FAILED,
            ]);

            OrderHistory::query()->create([
                'order_id' => $lockedOrder->id,
                'action' => 'vnpay_payment',
                'note' => 'VNPay: thanh toán không thành công',
                'changed_by' => null,
            ]);
        }

        return ['rsp_code' => '00', 'message' => 'Confirm Success', 'became_paid' => false];
    }

    public function renewVnpTxnRefAfterFailure(int $customerId, int $orderId): Order
    {
        return DB::transaction(function () use ($customerId, $orderId): Order {
            /** @var Order $order */
            $order = $this->orderModel->query()
                ->where('customer_id', $customerId)
                ->whereKey($orderId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->payment_method !== PaymentMethod::VNPAY) {
                throw new \RuntimeException('Đơn không áp dụng thanh toán VNPay.');
            }

            if ($order->payment_status !== PaymentStatus::FAILED) {
                throw new \RuntimeException('Chỉ có thể thanh toán lại khi thanh toán trước đó đã thất bại.');
            }

            if ($order->status === OrderStatus::CANCELLED) {
                throw new \RuntimeException('Đơn đã huỷ, không thể thanh toán lại.');
            }

            $order->update([
                'vnp_txn_ref' => $this->allocateUniqueVnpTxnRef(),
                'payment_status' => PaymentStatus::PENDING,
            ]);

            OrderHistory::query()->create([
                'order_id' => $order->id,
                'action' => 'vnpay_retry',
                'note' => 'Khách chủ động thanh toán lại qua VNPay sau giao dịch thất bại',
                'changed_by' => null,
            ]);

            return $order->fresh();
        });
    }

    private function allocateUniqueVnpTxnRef(): string
    {
        for ($i = 0; $i < 8; $i++) {
            $candidate = Str::upper(Str::random(20));
            $exists = $this->orderModel->query()->where('vnp_txn_ref', $candidate)->exists();
            if (! $exists) {
                return $candidate;
            }
        }

        return Str::upper(Str::uuid()->toString());
    }
}
