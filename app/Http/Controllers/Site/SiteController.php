<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\SiteService;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function __construct(
        private SiteService $siteService
    ) {}

    public function home(Request $request)
    {
        $keyword = trim((string) $request->input('keyword', ''));
        $categoryId = (int) $request->input('category_id', 0);
        $homeData = $this->siteService->getHomeData($request->query());

        return view('site.home', [
            'products' => $homeData['products'],
            'categories' => $homeData['categories'],
            'homeCategorySlides' => $homeData['homeCategorySlides'],
            'heroBannerBySide' => $homeData['heroBannerBySide'],
            'keyword' => $keyword,
            'categoryId' => $categoryId,
        ]);
    }

    public function showProduct(int $id)
    {
        $productData = $this->siteService->getProductDetailData($id);

        return view('site.product-show', [
            'product' => $productData['product'],
            'relatedProducts' => $productData['relatedProducts'],
        ]);
    }

    /**
     * Trang thông tin tài khoản khách hàng.
     */
    public function account()
    {
        /** @var Customer|null $customer */
        $customer = auth()->guard('customer')->user();

        return view('site.account', [
            'customer' => $customer,
        ]);
    }

    public function updateAccount(Request $request)
    {
        /** @var Customer|null $customer */
        $customer = auth()->guard('customer')->user();

        if (!$customer) {
            return redirect()->route('site.login')->with('dataError', 'Vui lòng đăng nhập để tiếp tục');
        }

        $payload = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [], [
            'full_name' => 'họ tên',
            'phone' => 'số điện thoại',
            'address' => 'địa chỉ',
        ]);

        $this->siteService->updateAccountProfile($customer, $payload);

        return redirect()
            ->route('site.account', ['tab' => 'info'])
            ->with('dataSuccess', 'Cập nhật thông tin thành công');
    }

    public function updateAccountPassword(Request $request)
    {
        /** @var Customer|null $customer */
        $customer = auth()->guard('customer')->user();

        if (!$customer) {
            return redirect()->route('site.login')->with('dataError', 'Vui lòng đăng nhập để tiếp tục');
        }

        $payload = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [], [
            'current_password' => 'mật khẩu hiện tại',
            'new_password' => 'mật khẩu mới',
        ]);

        $changed = $this->siteService->changeAccountPassword(
            $customer,
            $payload['current_password'],
            $payload['new_password']
        );

        if (!$changed) {
            return redirect()
                ->route('site.account', ['tab' => 'password'])
                ->with('dataError', 'Mật khẩu hiện tại không đúng');
        }

        return redirect()
            ->route('site.account', ['tab' => 'password'])
            ->with('dataSuccess', 'Đổi mật khẩu thành công');
    }
}
