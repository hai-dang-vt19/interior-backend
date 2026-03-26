<?php

namespace App\Http\Controllers\Site;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SiteCheckoutRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class SiteOrderController extends Controller
{
    public function checkout()
    {
        $cart = $this->resolveCart();
        $cart->load(['items.product']);
        if ($cart->items->isEmpty()) {
            return redirect()->route('site.cart.index')->with('dataError', 'Giỏ hàng đang trống');
        }

        $paymentMethods = PaymentMethod::cases();
        return view('site.order.checkout', compact('cart', 'paymentMethods'));
    }

    public function placeOrder(SiteCheckoutRequest $request)
    {
        $cart = $this->resolveCart();
        $cart->load(['items.product']);
        if ($cart->items->isEmpty()) {
            return redirect()->route('site.cart.index')->with('dataError', 'Giỏ hàng đang trống');
        }

        $payload = $request->validated();
        $customerId = auth()->guard('customer')->id();

        $order = DB::transaction(function () use ($cart, $payload, $customerId) {
            $total = 0;
            foreach ($cart->items as $item) {
                $product = Product::query()->lockForUpdate()->find($item->product_id);
                if (!$product || (int) $product->status->value !== ProductStatus::ACTIVE->value) {
                    throw new \RuntimeException('Sản phẩm không còn khả dụng: ' . ($item->product?->name ?? 'N/A'));
                }
                if ((int) $item->quantity > (int) $product->quantity) {
                    throw new \RuntimeException('Sản phẩm vượt tồn kho: ' . $product->name);
                }
                $total += ((int) $item->quantity) * ((float) $item->price);
            }

            $order = Order::query()->create([
                'customer_id' => $customerId,
                'total_amount' => $total,
                'shipping_address' => $payload['shipping_address'],
                'shipping_phone' => $payload['shipping_phone'],
                'status' => OrderStatus::PENDING,
                'payment_method' => (int) $payload['payment_method'],
                'payment_status' => PaymentStatus::PENDING,
                'notes' => $payload['notes'] ?? null,
            ]);

            foreach ($cart->items as $item) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);

                $product = Product::query()->find($item->product_id);
                $nextQty = max(0, ((int) $product->quantity) - ((int) $item->quantity));
                $product->quantity = $nextQty;
                if ($nextQty === 0) {
                    $product->status = ProductStatus::OUT_OF_STOCK;
                }
                $product->save();
            }

            $cart->items()->delete();
            return $order;
        });

        return redirect()->route('site.orders.show', $order->id)->with('dataSuccess', 'Đặt hàng thành công');
    }

    public function index()
    {
        $orders = Order::query()
            ->with('items')
            ->where('customer_id', auth()->guard('customer')->id())
            ->latest('id')
            ->paginate(10);

        return view('site.order.index', compact('orders'));
    }

    public function show(int $id)
    {
        $order = Order::query()
            ->with(['items.product.images'])
            ->where('customer_id', auth()->guard('customer')->id())
            ->findOrFail($id);

        return view('site.order.show', compact('order'));
    }

    private function resolveCart(): Cart
    {
        return Cart::query()->firstOrCreate([
            'customer_id' => auth()->guard('customer')->id(),
        ]);
    }
}
