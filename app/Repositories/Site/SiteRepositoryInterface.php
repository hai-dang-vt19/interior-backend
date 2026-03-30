<?php

declare(strict_types=1);

namespace App\Repositories\Site;

use App\Models\Product;
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
}
