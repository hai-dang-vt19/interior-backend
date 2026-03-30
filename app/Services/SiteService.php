<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Repositories\Site\SiteRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class SiteService extends BaseService
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository
    ) {}

    // Lấy dữ liệu trang chủ theo bộ lọc người dùng
    public function getHomeData(array $params): array
    {
        return [
            'categories' => $this->siteRepository->getCategoriesForHome(),
            'products' => $this->siteRepository->getFilteredProductsForHome($params),
            'homeCategorySlides' => $this->siteRepository->getHomeCategorySlides(),
            'heroBannerBySide' => $this->siteRepository->getHeroBannerBySide(),
        ];
    }

    // Lấy chi tiết sản phẩm và sản phẩm liên quan
    public function getProductDetailData(int $id): array
    {
        $product = $this->siteRepository->getProductDetail($id);

        return [
            'product' => $product,
            'relatedProducts' => $this->siteRepository->getRelatedProducts($product->id, (int) $product->category_id),
        ];
    }

    // Cập nhật thông tin tài khoản khách hàng
    public function updateAccountProfile(Customer $customer, array $payload): bool
    {
        return $this->siteRepository->updateCustomerProfile((int) $customer->id, $payload);
    }

    // Đổi mật khẩu tài khoản khách hàng
    public function changeAccountPassword(Customer $customer, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, (string) $customer->password)) {
            return false;
        }

        return $this->siteRepository->updateCustomerPassword((int) $customer->id, Hash::make($newPassword));
    }
}
