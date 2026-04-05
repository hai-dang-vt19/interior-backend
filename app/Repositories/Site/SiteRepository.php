<?php

declare(strict_types=1);

namespace App\Repositories\Site;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\HomeBannerProduct;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SiteRepository implements SiteRepositoryInterface
{
    public function __construct(
        private Category $categoryModel,
        private Product $productModel,
        private HomeBannerProduct $homeBannerProductModel,
        private Customer $customerModel,
        private CustomerAddress $customerAddressModel,
        private ProductReview $productReviewModel,
        private OrderItem $orderItemModel
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

    public function getCustomerAddressesOrdered(int $customerId): Collection
    {
        return $this->customerAddressModel->query()
            ->where('customer_id', $customerId)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();
    }

    public function findCustomerAddressOwned(int $customerId, int $addressId): ?CustomerAddress
    {
        return $this->customerAddressModel->query()
            ->where('customer_id', $customerId)
            ->where('id', $addressId)
            ->first();
    }

    public function createCustomerAddress(int $customerId, array $payload): CustomerAddress
    {
        $wantDefault = filter_var($payload['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $hasDefault = $this->customerAddressModel->query()
            ->where('customer_id', $customerId)
            ->where('is_default', true)
            ->exists();

        $setDefault = $wantDefault || !$hasDefault;
        if ($setDefault) {
            $this->customerAddressModel->query()->where('customer_id', $customerId)->update(['is_default' => false]);
        }

        return $this->customerAddressModel->query()->create([
            'customer_id' => $customerId,
            'address_line' => $payload['address_line'],
            'ward' => $payload['ward'] ?? null,
            'district' => $payload['district'] ?? null,
            'city' => $payload['city'] ?? null,
            'is_default' => $setDefault,
        ]);
    }

    public function updateCustomerAddress(int $customerId, int $addressId, array $payload): bool
    {
        $address = $this->findCustomerAddressOwned($customerId, $addressId);
        if (!$address) {
            return false;
        }

        $wasDefault = (bool) $address->is_default;
        $newDefault = filter_var($payload['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($newDefault) {
            $this->customerAddressModel->query()->where('customer_id', $customerId)->update(['is_default' => false]);
        }

        $address->update([
            'address_line' => $payload['address_line'],
            'ward' => $payload['ward'] ?? null,
            'district' => $payload['district'] ?? null,
            'city' => $payload['city'] ?? null,
            'is_default' => $newDefault,
        ]);

        if (!$newDefault && $wasDefault) {
            $other = $this->customerAddressModel->query()
                ->where('customer_id', $customerId)
                ->where('id', '!=', $addressId)
                ->orderByDesc('id')
                ->first();
            if ($other) {
                $this->customerAddressModel->query()->where('customer_id', $customerId)->update(['is_default' => false]);
                $other->update(['is_default' => true]);
            }
        }

        return true;
    }

    public function deleteCustomerAddress(int $customerId, int $addressId): bool
    {
        $address = $this->findCustomerAddressOwned($customerId, $addressId);
        if (!$address) {
            return false;
        }

        $wasDefault = (bool) $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $next = $this->customerAddressModel->query()
                ->where('customer_id', $customerId)
                ->orderByDesc('id')
                ->first();
            if ($next) {
                $this->customerAddressModel->query()->where('customer_id', $customerId)->update(['is_default' => false]);
                $next->update(['is_default' => true]);
            }
        }

        return true;
    }

    public function setDefaultCustomerAddress(int $customerId, int $addressId): bool
    {
        $address = $this->findCustomerAddressOwned($customerId, $addressId);
        if (!$address) {
            return false;
        }

        $this->customerAddressModel->query()->where('customer_id', $customerId)->update(['is_default' => false]);

        return $address->update(['is_default' => true]);
    }

    public function getDefaultShippingAddressText(int $customerId): ?string
    {
        $row = $this->customerAddressModel->query()
            ->where('customer_id', $customerId)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->first();

        if (!$row) {
            return null;
        }

        return collect([$row->address_line, $row->ward, $row->district, $row->city])
            ->map(fn ($v) => is_string($v) ? trim($v) : $v)
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->implode(', ');
    }

    public function getProductReviewsForProduct(int $productId): Collection
    {
        return $this->productReviewModel->query()
            ->with(['customer:id,full_name'])
            ->where('product_id', $productId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findCustomerReviewForProduct(int $customerId, int $productId): ?ProductReview
    {
        return $this->productReviewModel->query()
            ->where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->first();
    }

    public function customerHasDeliveredPurchaseForProduct(int $customerId, int $productId): bool
    {
        return $this->orderItemModel->query()
            ->where('product_id', $productId)
            ->whereHas('order', function ($q) use ($customerId) {
                $q->where('customer_id', $customerId)
                    ->where('status', OrderStatus::DELIVERED);
            })
            ->exists();
    }

    public function createProductReview(int $customerId, int $productId, array $payload): ProductReview
    {
        return $this->productReviewModel->query()->create([
            'customer_id' => $customerId,
            'product_id' => $productId,
            'review' => $payload['review'],
            'rating' => (int) $payload['rating'],
        ]);
    }

    public function updateCustomerProductReview(int $customerId, int $productId, int $reviewId, array $payload): bool
    {
        $review = $this->productReviewModel->query()
            ->where('id', $reviewId)
            ->where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->first();

        if (!$review) {
            return false;
        }

        return $review->update([
            'review' => $payload['review'],
            'rating' => (int) $payload['rating'],
        ]);
    }
}
