<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\SiteCheckoutRequest;
use App\Services\SiteOrderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class SiteOrderController extends Controller
{
    public function __construct(
        private SiteOrderService $siteOrderService
    ) {}

    public function checkout(Request $request)
    {
        $customerId = (int) auth()->guard('customer')->id();
        $selectedItemsCsv = (string) $request->query('selected_items', '');
        $checkoutData = $this->siteOrderService->getCheckoutData($customerId, $selectedItemsCsv);
        $cart = $checkoutData['cart'];
        $checkoutItems = $checkoutData['checkoutItems'];

        if ($cart->items->isEmpty()) {
            return redirect()->route('site.cart.index')->with('dataError', 'Giỏ hàng đang trống');
        }
        if ($checkoutItems->isEmpty()) {
            return redirect()->route('site.cart.index')->with('dataError', 'Vui lòng chọn ít nhất 1 sản phẩm để thanh toán');
        }

        $paymentMethods = $checkoutData['paymentMethods'];
        $defaultShippingAddress = $checkoutData['defaultShippingAddress'];

        return view('site.order.checkout', compact('cart', 'paymentMethods', 'checkoutItems', 'selectedItemsCsv', 'defaultShippingAddress'));
    }

    public function placeOrder(SiteCheckoutRequest $request)
    {
        $payload = $request->validated();
        $customerId = (int) auth()->guard('customer')->id();
        try {
            $order = $this->siteOrderService->placeOrder($customerId, $payload);
        } catch (\RuntimeException $e) {
            return redirect()->route('site.cart.index')->with('dataError', $e->getMessage());
        }

        return redirect()->route('site.orders.show', $order->id)->with('dataSuccess', 'Đặt hàng thành công');
    }

    public function index(Request $request)
    {
        $status = (int) $request->query('status', 0);
        $filters = [];
        if ($status > 0) {
            $filters['status'] = $status;
        }
        $orders = $this->siteOrderService->getOrdersByCustomer((int) auth()->guard('customer')->id(), $filters);

        return view('site.order.index', compact('orders', 'status'));
    }

    public function show(int $id)
    {
        $order = $this->siteOrderService->getOrderDetailByCustomer((int) auth()->guard('customer')->id(), $id);

        return view('site.order.show', compact('order'));
    }

    public function reorder(int $id)
    {
        $addedCount = $this->siteOrderService->reorderItems((int) auth()->guard('customer')->id(), $id);

        if ($addedCount < 1) {
            return redirect()->route('site.orders.show', $id)->with('dataError', 'Khong co san pham kha dung de mua lai');
        }

        return redirect()->route('site.cart.index')->with('dataSuccess', 'Da them '.$addedCount.' san pham vao gio hang');
    }

    public function cancel(int $id)
    {
        $customerId = (int) auth()->guard('customer')->id();

        try {
            $this->siteOrderService->cancelOrderByCustomer($customerId, $id);
        } catch (ModelNotFoundException $e) {
            abort(404);
        } catch (\RuntimeException $e) {
            return redirect()->route('site.orders.show', $id)->with('dataError', $e->getMessage());
        }

        return redirect()->route('site.orders.show', $id)->with('dataSuccess', 'Đã huỷ đơn hàng.');
    }
}
