<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\SiteAccountAddressRequest;
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
        $customerId = auth()->guard('customer')->id();
        $productData = $this->siteService->getProductDetailData(
            $id,
            $customerId !== null ? (int) $customerId : null
        );

        return view('site.product-show', $productData);
    }

    public function products(Request $request)
    {
        $keyword = trim((string) $request->input('keyword', ''));
        $categoryId = (int) $request->input('category_id', 0);
        $minPrice = $request->filled('min_price') ? (float) $request->input('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->input('max_price') : null;
        $sort = trim((string) $request->input('sort', 'newest'));
        $homeData = $this->siteService->getHomeData($request->query());

        return view('site.products', [
            'products' => $homeData['products'],
            'categories' => $homeData['categories'],
            'keyword' => $keyword,
            'categoryId' => $categoryId,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'sort' => $sort,
        ]);
    }

    /**
     * Trang thông tin tài khoản khách hàng.
     */
    public function account()
    {
        /** @var Customer|null $customer */
        $customer = auth()->guard('customer')->user();

        $addresses = $this->siteService->getCustomerAddresses((int) $customer->id);

        return view('site.account', [
            'customer' => $customer,
            'addresses' => $addresses,
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
        ], [], [
            'full_name' => 'họ tên',
            'phone' => 'số điện thoại',
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

    public function storeAccountAddress(SiteAccountAddressRequest $request)
    {
        /** @var Customer|null $customer */
        $customer = auth()->guard('customer')->user();
        if (!$customer) {
            return redirect()->route('site.login')->with('dataError', 'Vui lòng đăng nhập để tiếp tục');
        }

        $payload = $request->validated();
        $payload['is_default'] = $request->boolean('is_default');
        $this->siteService->storeCustomerAddress((int) $customer->id, $payload);

        return redirect()
            ->route('site.account', ['tab' => 'addresses'])
            ->with('dataSuccess', 'Đã thêm địa chỉ');
    }

    public function updateAccountAddress(SiteAccountAddressRequest $request, int $id)
    {
        /** @var Customer|null $customer */
        $customer = auth()->guard('customer')->user();
        if (!$customer) {
            return redirect()->route('site.login')->with('dataError', 'Vui lòng đăng nhập để tiếp tục');
        }

        $payload = $request->validated();
        $payload['is_default'] = $request->boolean('is_default');
        $ok = $this->siteService->updateCustomerAddress((int) $customer->id, $id, $payload);

        if (!$ok) {
            return redirect()
                ->route('site.account', ['tab' => 'addresses'])
                ->with('dataError', 'Không tìm thấy địa chỉ');
        }

        return redirect()
            ->route('site.account', ['tab' => 'addresses'])
            ->with('dataSuccess', 'Đã cập nhật địa chỉ');
    }

    public function destroyAccountAddress(int $id)
    {
        /** @var Customer|null $customer */
        $customer = auth()->guard('customer')->user();
        if (!$customer) {
            return redirect()->route('site.login')->with('dataError', 'Vui lòng đăng nhập để tiếp tục');
        }

        $ok = $this->siteService->deleteCustomerAddress((int) $customer->id, $id);
        if (!$ok) {
            return redirect()
                ->route('site.account', ['tab' => 'addresses'])
                ->with('dataError', 'Không tìm thấy địa chỉ');
        }

        return redirect()
            ->route('site.account', ['tab' => 'addresses'])
            ->with('dataSuccess', 'Đã xóa địa chỉ');
    }

    public function setDefaultAccountAddress(int $id)
    {
        /** @var Customer|null $customer */
        $customer = auth()->guard('customer')->user();
        if (!$customer) {
            return redirect()->route('site.login')->with('dataError', 'Vui lòng đăng nhập để tiếp tục');
        }

        $ok = $this->siteService->setDefaultCustomerAddress((int) $customer->id, $id);
        if (!$ok) {
            return redirect()
                ->route('site.account', ['tab' => 'addresses'])
                ->with('dataError', 'Không tìm thấy địa chỉ');
        }

        return redirect()
            ->route('site.account', ['tab' => 'addresses'])
            ->with('dataSuccess', 'Đã đặt địa chỉ mặc định');
    }
}
