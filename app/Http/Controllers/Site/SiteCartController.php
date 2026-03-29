<?php

namespace App\Http\Controllers\Site;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SiteCartItemStoreRequest;
use App\Http\Requests\SiteCartItemUpdateRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class SiteCartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $this->resolveCart();
        $cart->load(['items.product.images']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Lấy giỏ hàng thành công',
                'data' => $this->formatCartPayload($cart),
            ]);
        }

        return view('site.cart.index', compact('cart'));
    }

    public function store(SiteCartItemStoreRequest $request)
    {
        $payload = $request->validated();
        $product = Product::query()->findOrFail($payload['product_id']);
        if ((int) $product->status->value !== ProductStatus::ACTIVE->value) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sản phẩm hiện không khả dụng',
                ], 422);
            }
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
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Số lượng vượt quá tồn kho',
                    ], 422);
                }
                return redirect()->back()->with('dataError', 'Số lượng vượt quá tồn kho');
            }
            $item->update([
                'quantity' => $nextQty,
                'price' => $price,
            ]);
        } else {
            if ($quantity > (int) $product->quantity) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Số lượng vượt quá tồn kho',
                    ], 422);
                }
                return redirect()->back()->with('dataError', 'Số lượng vượt quá tồn kho');
            }
            CartItem::query()->create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $price,
            ]);
        }

        if ($request->expectsJson()) {
            $cart->load(['items.product.images']);

            return response()->json([
                'message' => 'Đã thêm vào giỏ hàng',
                'data' => $this->formatCartPayload($cart),
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
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Số lượng vượt quá tồn kho',
                ], 422);
            }

            return redirect()->back()->with('dataError', 'Số lượng vượt quá tồn kho');
        }

        $item->update(['quantity' => $quantity]);

        if ($request->expectsJson()) {
            $cart = $this->resolveCart();
            $cart->load(['items.product.images']);

            return response()->json([
                'message' => 'Đã cập nhật giỏ hàng',
                'data' => $this->formatCartPayload($cart),
            ]);
        }

        return redirect()->back()->with('dataSuccess', 'Đã cập nhật giỏ hàng');
    }

    public function destroy(Request $request, int $id)
    {
        $item = CartItem::query()
            ->where('id', $id)
            ->whereHas('cart', function ($query) {
                $query->where('customer_id', auth()->guard('customer')->id());
            })
            ->firstOrFail();

        $item->delete();

        if ($request->expectsJson()) {
            $cart = $this->resolveCart();
            $cart->load(['items.product.images']);

            return response()->json([
                'message' => 'Đã xóa sản phẩm khỏi giỏ',
                'data' => $this->formatCartPayload($cart),
            ]);
        }

        return redirect()->back()->with('dataSuccess', 'Đã xóa sản phẩm khỏi giỏ');
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

    private function resolveCart(): Cart
    {
        return Cart::query()->firstOrCreate([
            'customer_id' => auth()->guard('customer')->id(),
        ]);
    }
}
