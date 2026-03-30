<?php

declare(strict_types=1);

namespace App\Repositories\Site;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Customer;
use App\Models\HomeBannerProduct;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SiteRepository implements SiteRepositoryInterface
{
    public function __construct(
        private Category $categoryModel,
        private Product $productModel,
        private HomeBannerProduct $homeBannerProductModel,
        private Customer $customerModel
    ) {}

    public function getCategoriesForHome(): Collection
    {
        return $this->categoryModel->query()->orderBy('name')->get(['id', 'name']);
    }

    public function getFilteredProductsForHome(array $params): LengthAwarePaginator
    {
        $keyword = trim((string) ($params['keyword'] ?? ''));
        $categoryId = (int) ($params['category_id'] ?? 0);

        return $this->productModel->query()
            ->with(['category:id,name', 'images'])
            ->where('status', ProductStatus::ACTIVE->value)
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where('name', 'like', '%'.$keyword.'%');
            })
            ->when($categoryId > 0, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->orderByDesc('id')
            ->paginate(12)
            ->appends($params);
    }

    public function getHomeCategorySlides(): array
    {
        $homeCategorySlides = [];
        $slideCategories = $this->categoryModel->query()
            ->whereHas('products', fn ($q) => $q->where('status', ProductStatus::ACTIVE->value))
            ->orderBy('name')
            ->limit(2)
            ->get(['id', 'name']);

        foreach ($slideCategories as $cat) {
            $homeCategorySlides[] = [
                'category' => $cat,
                'products' => $this->productModel->query()
                    ->with('images')
                    ->where('status', ProductStatus::ACTIVE->value)
                    ->where('category_id', $cat->id)
                    ->orderByDesc('id')
                    ->limit(12)
                    ->get(),
            ];
        }

        return $homeCategorySlides;
    }

    public function getHeroBannerBySide(): array
    {
        $heroBannerItems = $this->homeBannerProductModel->query()
            ->with([
                'product' => function ($query) {
                    $query->with('images')
                        ->where('status', ProductStatus::ACTIVE->value)
                        ->whereNull('deleted_at');
                },
            ])
            ->orderBy('side')
            ->orderBy('position')
            ->get()
            ->filter(fn ($item) => $item->product !== null)
            ->values();

        return [
            'left' => $heroBannerItems->where('side', 'left')->values(),
            'right' => $heroBannerItems->where('side', 'right')->values(),
        ];
    }

    public function getProductDetail(int $id): Product
    {
        return $this->productModel->query()
            ->with(['category:id,name', 'images'])
            ->where('status', ProductStatus::ACTIVE->value)
            ->findOrFail($id);
    }

    public function getRelatedProducts(int $productId, int $categoryId): Collection
    {
        return $this->productModel->query()
            ->with('images')
            ->where('status', ProductStatus::ACTIVE->value)
            ->where('category_id', $categoryId)
            ->where('id', '!=', $productId)
            ->orderByDesc('id')
            ->limit(4)
            ->get();
    }

    public function updateCustomerProfile(int $customerId, array $payload): bool
    {
        return $this->customerModel->query()->where('id', $customerId)->update($payload) > 0;
    }

    public function updateCustomerPassword(int $customerId, string $hashedPassword): bool
    {
        return $this->customerModel->query()
            ->where('id', $customerId)
            ->update(['password' => $hashedPassword]) > 0;
    }
}
