<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\VnpayCallbackService;
use Illuminate\Http\Request;

class SiteVnpayController extends Controller
{
    public function __construct(
        private VnpayCallbackService $vnpayCallbackService
    ) {}

    public function return(Request $request)
    {
        $customerId = (int) auth()->guard('customer')->id();

        try {
            return $this->vnpayCallbackService->handleReturn($request, $customerId);
        } catch (\RuntimeException $e) {
            return redirect()->route('site.orders.index')->with('dataError', $e->getMessage());
        }
    }

    public function ipn(Request $request)
    {
        return $this->vnpayCallbackService->handleIpn($request);
    }
}
