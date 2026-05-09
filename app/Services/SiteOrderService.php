<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Support\CustomerLoyalty;
use App\Support\CustomerOrderNotifier;
use App\Repositories\Site\SiteRepositoryInterface;
use App\Repositories\SiteOrder\SiteOrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SiteOrderService extends BaseService
{
    public function __construct(
        private SiteOrderRepositoryInterface $siteOrderRepository,
        private SiteRepositoryInterface $siteRepository,
        private SiteCartService $siteCartService
    ) {}

    public function getCheckoutData(int $customerId, string $selectedItemsCsv): array
    {
        $cart = $this->siteOrderRepository->getCartWithItems($customerId);
        $this->siteCartService->syncCartLinePrices($cart);
        $normalizedCsv = $this->normalizedSelectedCartItemIdsCsv($cart, $selectedItemsCsv);
        $checkoutItems = $this->siteOrderRepository->getCheckoutItems($cart, $normalizedCsv);

        $customerRow = Customer::query()->find($customerId);
        $tier = (string) ($customerRow?->loyalty_tier ?? 'standard');
        $checkoutSubtotal = $checkoutItems->sum(static fn ($item) => ((float) $item->price) * ((int) $item->quantity));
        $loyaltyDiscountPreview = CustomerLoyalty::computeDiscountAmountFromSubtotal($checkoutSubtotal, $tier);
        $checkoutGrandTotal = max(0, (int) round($checkoutSubtotal - $loyaltyDiscountPreview));

        return [
            'cart' => $cart,
            'checkoutItems' => $checkoutItems,
            'paymentMethods' => PaymentMethod::cases(),
            'selectedItemsCsv' => $normalizedCsv,
            'defaultShippingAddress' => $this->siteRepository->getDefaultShippingAddressText($customerId),
            'checkoutSubtotal' => $checkoutSubtotal,
            'loyaltyDiscountAmount' => $loyaltyDiscountPreview,
            'checkoutGrandTotal' => $checkoutGrandTotal,
            'loyaltyTierDisplay' => $customerRow !== null ? $customerRow->formatLoyaltyTier() : null,
            'loyaltyBenefitLine' => CustomerLoyalty::benefitLabel($tier),
        ];
    }

    public function placeOrder(int $customerId, array $payload): Order
    {
        $cart = $this->siteOrderRepository->getCartWithItems($customerId);
        $this->siteCartService->syncCartLinePrices($cart);
        $normalizedCsv = $this->normalizedSelectedCartItemIdsCsv($cart, (string) ($payload['selected_items'] ?? ''));
        $checkoutItems = $this->siteOrderRepository->getCheckoutItems($cart, $normalizedCsv);

        if ($cart->items->isEmpty()) {
            throw new \RuntimeException('Giỏ hàng đang trống');
        }
        if ($checkoutItems->isEmpty()) {
            throw new \RuntimeException('Vui lòng chọn ít nhất 1 sản phẩm để thanh toán');
        }

        return $this->siteOrderRepository->createOrderFromCheckout($customerId, $cart, $checkoutItems, $payload);
    }

    public function getOrdersByCustomer(int $customerId, array $filters = []): LengthAwarePaginator
    {
        return $this->siteOrderRepository->getOrdersByCustomer($customerId, $filters);
    }

    public function getOrderDetailByCustomer(int $customerId, int $orderId): Order
    {
        return $this->siteOrderRepository->getOrderDetailByCustomer($customerId, $orderId);
    }

    public function reorderItems(int $customerId, int $orderId): int
    {
        return $this->siteOrderRepository->reorderItems($customerId, $orderId);
    }

    public function cancelOrderByCustomer(int $customerId, int $orderId): Order
    {
        $order = $this->siteOrderRepository->cancelOrderByCustomer($customerId, $orderId);
        CustomerOrderNotifier::sendOrderUpdatedEmail($order, CustomerOrderNotifier::CONTEXT_CUSTOMER_CANCEL);

        return $order;
    }

    /** Khi không truyền selected_items (ví dụ vào checkout từ trang giỏ), mặc định lấy toàn bộ dòng trong giỏ. */
    private function normalizedSelectedCartItemIdsCsv(Cart $cart, string $rawCsv): string
    {
        $trimmed = trim($rawCsv);
        if ($trimmed !== '') {
            return $trimmed;
        }

        if (! $cart->relationLoaded('items')) {
            $cart->loadMissing('items');
        }

        if ($cart->items->isEmpty()) {
            return '';
        }

        return $cart->items->pluck('id')->implode(',');
    }
}
