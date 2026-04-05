<?php

declare(strict_types=1);

namespace App\Repositories\Site;

use App\Models\CustomerAddress;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SiteRepositoryInterface
{
    public function getCategoriesForHome(): Collection;
    public function getFilteredProductsForHome(array $params): LengthAwarePaginator;
    public function getHomeCategorySlides(): array;
    public function getHeroBannerBySide(): array;
    public function getProductDetail(int $id): Product;
    public function getRelatedProducts(int $productId, int $categoryId): Collection;
    public function updateCustomerProfile(int $customerId, array $payload): bool;
    public function updateCustomerPassword(int $customerId, string $hashedPassword): bool;
    public function getCustomerAddressesOrdered(int $customerId): Collection;
    public function findCustomerAddressOwned(int $customerId, int $addressId): ?CustomerAddress;
    public function createCustomerAddress(int $customerId, array $payload): CustomerAddress;
    public function updateCustomerAddress(int $customerId, int $addressId, array $payload): bool;
    public function deleteCustomerAddress(int $customerId, int $addressId): bool;
    public function setDefaultCustomerAddress(int $customerId, int $addressId): bool;
    public function getDefaultShippingAddressText(int $customerId): ?string;

    /** Danh sách đánh giá hiển thị công khai trên trang sản phẩm */
    public function getProductReviewsForProduct(int $productId): Collection;

    /** Đánh giá của một khách cho sản phẩm (nếu có) */
    public function findCustomerReviewForProduct(int $customerId, int $productId): ?ProductReview;

    /** Khách đã có đơn hàng trạng thái đã giao chứa sản phẩm này */
    public function customerHasDeliveredPurchaseForProduct(int $customerId, int $productId): bool;

    /** Tạo đánh giá mới (mỗi khách tối đa 1 bản ghi / sản phẩm) */
    public function createProductReview(int $customerId, int $productId, array $payload): ProductReview;

    /** Cập nhật đánh giá do chính khách tạo */
    public function updateCustomerProductReview(int $customerId, int $productId, int $reviewId, array $payload): bool;
}
