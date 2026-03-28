<?php

declare(strict_types=1);

namespace App\Repositories\Product;

use App\Enums\PerPage;
use App\Enums\InventoryType;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\Product;
use App\Models\ProductImage;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private Product $model
    ) {}

    public function getProducts(array $params): LengthAwarePaginator
    {
        $products = $this->model
            ->withTrashed()
            ->with(['category', 'images'])
            ->when(isset($params['name']), function (Builder $query) use ($params) {
                return $query->where('name', 'like', '%' . $params['name'] . '%');
            })
            ->when(isset($params['category_id']) && $params['category_id'] !== '', function (Builder $query) use ($params) {
                return $query->where('category_id', (int) $params['category_id']);
            })
            ->when(isset($params['status']) && $params['status'] !== '', function (Builder $query) use ($params) {
                return $query->where('status', (int) $params['status']);
            })
            ->when(isset($params['dateFrom']) && $params['dateFrom'] !== '', function (Builder $query) use ($params) {
                $dates = explode(' - ', $params['dateFrom']);
                if (count($dates) === 2) {
                    return $query->whereBetween('created_at', [
                        Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay(),
                        Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay(),
                    ]);
                }

                $date = Carbon::createFromFormat('d/m/Y', $params['dateFrom']);

                return $query->whereDate('created_at', $date->format('Y-m-d'));
            })
            ->when(($params['deleted'] ?? 'active') === 'active', function (Builder $query) {
                return $query->whereNull('deleted_at');
            })
            ->when(($params['deleted'] ?? 'active') === 'trashed', function (Builder $query) {
                return $query->onlyTrashed();
            });

        return $products
            ->orderByDesc('id')
            ->paginate(isset($params['per_page']) ? $params['per_page'] : PerPage::PER_PAGE_10->value)
            ->withQueryString();
    }

    public function getCategories(): Collection
    {
        return Category::query()->orderBy('name')->get();
    }

    public function createProduct(array $params): Product
    {
        return $this->model->create($params);
    }

    public function getProductByID(int $id): Product
    {
        return $this->model->withTrashed()->findOrFail($id);
    }

    public function updateProductByID(int $id, array $params): bool
    {
        return $this->model->withTrashed()->findOrFail($id)->update($params);
    }

    public function destroy(int $id): void
    {
        $this->model->findOrFail($id)->delete();
    }

    public function restore(int $id): bool
    {
        return (bool) $this->model->withTrashed()->findOrFail($id)->restore();
    }

    public function forceDelete(int $id): bool
    {
        $product = $this->model->withTrashed()->with('images')->findOrFail($id);

        foreach ($product->images as $image) {
            $this->removeProductImageFile($image->image_url);
        }

        return (bool) $product->forceDelete();
    }

    public function getProductImages(int $productId): Product
    {
        return $this->model->with(['images' => function ($query) {
            $query->orderByDesc('is_primary')->orderByDesc('id');
        }])->withTrashed()->findOrFail($productId);
    }

    public function addProductImage(int $productId, array $params): ProductImage
    {
        return ProductImage::query()->create([
            'product_id' => $productId,
            'image_url' => $params['image_url'],
            'is_primary' => false,
        ]);
    }

    /**
     * Đặt một ảnh làm primary, gỡ primary các ảnh khác của cùng sản phẩm.
     */
    public function setPrimaryProductImage(int $productId, int $imageId): bool
    {
        return (bool) DB::transaction(function () use ($productId, $imageId) {
            $exists = ProductImage::query()
                ->where('product_id', $productId)
                ->where('id', $imageId)
                ->exists();

            if (! $exists) {
                return false;
            }

            ProductImage::query()->where('product_id', $productId)->update(['is_primary' => false]);
            ProductImage::query()
                ->where('product_id', $productId)
                ->where('id', $imageId)
                ->update(['is_primary' => true]);

            return true;
        });
    }

    public function deleteProductImage(int $productId, int $imageId): bool
    {
        $image = ProductImage::query()
            ->where('product_id', $productId)
            ->where('id', $imageId)
            ->first();

        if (! $image) {
            return false;
        }

        $wasPrimary = (bool) $image->is_primary;
        $this->removeProductImageFile($image->image_url);
        $deleted = (bool) $image->delete();

        if ($deleted && $wasPrimary) {
            $next = ProductImage::query()
                ->where('product_id', $productId)
                ->orderByDesc('id')
                ->first();
            $next?->update(['is_primary' => true]);
        }

        return $deleted;
    }

    /**
     * Xóa file ảnh trên disk public (nhiều ảnh cùng thư mục product/{id} — chỉ xóa file, không xóa cả thư mục).
     */
    private function removeProductImageFile(?string $storedPath): void
    {
        if ($storedPath === null || $storedPath === '' || preg_match('#^https?://#i', $storedPath)) {
            return;
        }

        Storage::disk('public')->delete($storedPath);
    }

    public function getProductInventory(int $productId): Product
    {
        return $this->model
            ->with([
                'inventory',
                'inventoryHistories' => function ($query) {
                    $query->orderByDesc('id')->limit(50);
                },
            ])
            ->withTrashed()
            ->findOrFail($productId);
    }

    public function adjustInventory(int $productId, array $params, int $createdBy): bool
    {
        return DB::transaction(function () use ($productId, $params, $createdBy) {
            $product = $this->model->withTrashed()->findOrFail($productId);
            $inventory = Inventory::query()->firstOrCreate(
                ['product_id' => $product->id],
                ['quantity' => $product->quantity]
            );

            $delta = (int) $params['quantity'];
            $current = (int) $inventory->quantity;
            $newQuantity = $current;

            if ((int) $params['type'] === InventoryType::IMPORT->value) {
                $newQuantity = $current + $delta;
            } elseif ((int) $params['type'] === InventoryType::EXPORT->value) {
                $newQuantity = max(0, $current - $delta);
            } else {
                $newQuantity = $delta;
            }

            $inventory->update(['quantity' => $newQuantity]);
            $product->update(['quantity' => $newQuantity]);

            InventoryHistory::query()->create([
                'product_id' => $product->id,
                'type' => (int) $params['type'],
                'quantity' => $delta,
                'notes' => $params['notes'] ?? null,
                'created_by' => $createdBy,
            ]);

            return true;
        });
    }
}
