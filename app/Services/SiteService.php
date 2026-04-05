<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\ProductReview;
use App\Repositories\Site\SiteRepositoryInterface;
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

    // Lấy chi tiết sản phẩm, đánh giá và điều kiện gửi đánh giá (khi có customerId)
    public function getProductDetailData(int $id, ?int $customerId = null): array
    {
        $product = $this->siteRepository->getProductDetail($id);
        $canReviewAfterPurchase = false;
        $myProductReview = null;
        if ($customerId !== null) {
            $canReviewAfterPurchase = $this->siteRepository->customerHasDeliveredPurchaseForProduct($customerId, $id);
            $myProductReview = $this->siteRepository->findCustomerReviewForProduct($customerId, $id);
        }

        return [
            'product' => $product,
            'relatedProducts' => $this->siteRepository->getRelatedProducts($product->id, (int) $product->category_id),
            'productReviews' => $this->siteRepository->getProductReviewsForProduct($id),
            'canReviewAfterPurchase' => $canReviewAfterPurchase,
            'myProductReview' => $myProductReview,
        ];
    }

    /** Khách đã giao hàng và chưa có đánh giá thì tạo mới */
    public function storeProductReview(int $customerId, int $productId, array $payload): ?ProductReview
    {
        if (!$this->siteRepository->customerHasDeliveredPurchaseForProduct($customerId, $productId)) {
            return null;
        }
        if ($this->siteRepository->findCustomerReviewForProduct($customerId, $productId)) {
            return null;
        }
        $this->siteRepository->getProductDetail($productId);

        return $this->siteRepository->createProductReview($customerId, $productId, $payload);
    }

    /** Cập nhật đánh giá của chính khách */
    public function updateProductReview(int $customerId, int $productId, int $reviewId, array $payload): bool
    {
        if (!$this->siteRepository->customerHasDeliveredPurchaseForProduct($customerId, $productId)) {
            return false;
        }

        return $this->siteRepository->updateCustomerProductReview($customerId, $productId, $reviewId, $payload);
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

    public function getCustomerAddresses(int $customerId): Collection
    {
        return $this->siteRepository->getCustomerAddressesOrdered($customerId);
    }

    public function storeCustomerAddress(int $customerId, array $payload): CustomerAddress
    {
        return $this->siteRepository->createCustomerAddress($customerId, $payload);
    }

    public function updateCustomerAddress(int $customerId, int $addressId, array $payload): bool
    {
        return $this->siteRepository->updateCustomerAddress($customerId, $addressId, $payload);
    }

    public function deleteCustomerAddress(int $customerId, int $addressId): bool
    {
        return $this->siteRepository->deleteCustomerAddress($customerId, $addressId);
    }

    public function setDefaultCustomerAddress(int $customerId, int $addressId): bool
    {
        return $this->siteRepository->setDefaultCustomerAddress($customerId, $addressId);
    }
}
