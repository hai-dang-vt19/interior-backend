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

    public function lockOrderByVnpTxnRef(string $txnRef): ?Order;

    /**
     * @return array{rsp_code: string, message: string, became_paid: bool}
     */
    public function finalizeVnpayOnlinePayment(Order $lockedOrder, int $vnpScaledAmount, bool $paidByGateway, ?string $vnpTransactionNo): array;

    /**
     * Chuẩn bị thanh toán lại VNPay: mã giao dịch mới và trạng thái chờ thanh toán (sau khi lần trước thất bại).
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function renewVnpTxnRefAfterFailure(int $customerId, int $orderId): Order;
}
