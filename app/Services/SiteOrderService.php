<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Repositories\Site\SiteRepositoryInterface;
use App\Repositories\SiteOrder\SiteOrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SiteOrderService extends BaseService
{
    public function __construct(
        private SiteOrderRepositoryInterface $siteOrderRepository,
        private SiteRepositoryInterface $siteRepository
    ) {}

    public function getCheckoutData(int $customerId, string $selectedItemsCsv): array
    {
        $cart = $this->siteOrderRepository->getCartWithItems($customerId);
        $checkoutItems = $this->siteOrderRepository->getCheckoutItems($cart, $selectedItemsCsv);

        return [
            'cart' => $cart,
            'checkoutItems' => $checkoutItems,
            'paymentMethods' => PaymentMethod::cases(),
            'selectedItemsCsv' => $selectedItemsCsv,
            'defaultShippingAddress' => $this->siteRepository->getDefaultShippingAddressText($customerId),
        ];
    }

    public function placeOrder(int $customerId, array $payload): Order
    {
        $cart = $this->siteOrderRepository->getCartWithItems($customerId);
        $checkoutItems = $this->siteOrderRepository->getCheckoutItems($cart, (string) ($payload['selected_items'] ?? ''));

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
}
