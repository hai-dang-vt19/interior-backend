<?php

namespace App\Http\Controllers\Site;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SiteCartItemStoreRequest;
use App\Http\Requests\SiteCartItemUpdateRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

class SiteCartController extends Controller
{
    public function index()
    {
        $cart = $this->resolveCart();
        $cart->load(['items.product.images']);

        return view('site.cart.index', compact('cart'));
    }

    public function store(SiteCartItemStoreRequest $request)
    {
        $payload = $request->validated();
        $product = Product::query()->findOrFail($payload['product_id']);
        if ((int) $product->status->value !== ProductStatus::ACTIVE->value) {
            return redirect()->back()->with('dataError', 'Sản phẩm hiện không khả dụng');
        }

        $cart = $this->resolveCart();
        $quantity = (int) $payload['quantity'];
        $price = (float) ($product->discount_price ?? $product->price);

        $item = CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            $nextQty = (int) $item->quantity + $quantity;
            if ($nextQty > (int) $product->quantity) {
                return redirect()->back()->with('dataError', 'Số lượng vượt quá tồn kho');
            }
            $item->update([
                'quantity' => $nextQty,
                'price' => $price,
            ]);
        } else {
            if ($quantity > (int) $product->quantity) {
                return redirect()->back()->with('dataError', 'Số lượng vượt quá tồn kho');
            }
            CartItem::query()->create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $price,
            ]);
        }

        return redirect()->back()->with('dataSuccess', 'Đã thêm vào giỏ hàng');
    }

    public function update(SiteCartItemUpdateRequest $request, int $id)
    {
        $item = CartItem::query()
            ->where('id', $id)
            ->whereHas('cart', function ($query) {
                $query->where('customer_id', auth()->guard('customer')->id());
            })
            ->with('product')
            ->firstOrFail();

        $quantity = (int) $request->validated()['quantity'];
        if ($quantity > (int) $item->product->quantity) {
            return redirect()->back()->with('dataError', 'Số lượng vượt quá tồn kho');
        }

        $item->update(['quantity' => $quantity]);
        return redirect()->back()->with('dataSuccess', 'Đã cập nhật giỏ hàng');
    }

    public function destroy(int $id)
    {
        $item = CartItem::query()
            ->where('id', $id)
            ->whereHas('cart', function ($query) {
                $query->where('customer_id', auth()->guard('customer')->id());
            })
            ->firstOrFail();

        $item->delete();
        return redirect()->back()->with('dataSuccess', 'Đã xóa sản phẩm khỏi giỏ');
    }

    private function resolveCart(): Cart
    {
        return Cart::query()->firstOrCreate([
            'customer_id' => auth()->guard('customer')->id(),
        ]);
    }
}
