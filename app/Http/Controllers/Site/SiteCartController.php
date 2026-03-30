<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\SiteCartItemStoreRequest;
use App\Http\Requests\SiteCartItemUpdateRequest;
use App\Services\SiteCartService;
use Illuminate\Http\Request;

class SiteCartController extends Controller
{
    public function __construct(
        private SiteCartService $siteCartService
    ) {}

    public function index(Request $request)
    {
        $customerId = (int) auth()->guard('customer')->id();
        $payload = $this->siteCartService->getCartPayloadByCustomer($customerId);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Lấy giỏ hàng thành công',
                'data' => $payload,
            ]);
        }

        $cart = $this->siteCartService->getCartModelByCustomer($customerId);

        return view('site.cart.index', compact('cart'));
    }

    public function store(SiteCartItemStoreRequest $request)
    {
        $payload = $request->validated();
        $customerId = (int) auth()->guard('customer')->id();
        $result = $this->siteCartService->addItem(
            $customerId,
            (int) $payload['product_id'],
            (int) $payload['quantity']
        );

        if (!$result['ok']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $result['message'],
                ], 422);
            }

            return redirect()->back()->with('dataError', $result['message']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $result['message'],
                'data' => $result['data'],
            ]);
        }

        return redirect()->back()->with('dataSuccess', $result['message']);
    }

    public function update(SiteCartItemUpdateRequest $request, int $id)
    {
        $customerId = (int) auth()->guard('customer')->id();
        $quantity = (int) $request->validated()['quantity'];
        $result = $this->siteCartService->updateItemQuantity($customerId, $id, $quantity);
        if (!$result['ok']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $result['message'],
                ], 422);
            }

            return redirect()->back()->with('dataError', $result['message']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $result['message'],
                'data' => $result['data'],
            ]);
        }

        return redirect()->back()->with('dataSuccess', $result['message']);
    }

    public function destroy(Request $request, int $id)
    {
        $customerId = (int) auth()->guard('customer')->id();
        $result = $this->siteCartService->removeItem($customerId, $id);
        if (!$result['ok']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $result['message'],
                ], 422);
            }

            return redirect()->back()->with('dataError', $result['message']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $result['message'],
                'data' => $result['data'],
            ]);
        }

        return redirect()->back()->with('dataSuccess', $result['message']);
    }
}
