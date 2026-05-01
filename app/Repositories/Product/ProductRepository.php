<?php

declare(strict_types=1);

namespace App\Repositories\Product;

use App\Enums\PerPage;
use App\Enums\InventoryType;
use App\Models\Category;
use App\Models\HomeBannerProduct;
use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSpec;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private Product $model
    ) {}

    public function getProducts(array $params): LengthAwarePaginator
    {
        $products = $this->model
            ->withTrashed()
            ->with([
                'category',
                'images',
                'variants' => function ($query) {
                    $query->orderByDesc('is_default')->orderBy('id');
                },
                'specs' => function ($query) {
                    $query->orderBy('sort_order')->orderBy('id');
                },
            ])
            ->when(isset($params['name']), function (Builder $query) use ($params) {
                return $query->where('name', 'like', '%' . $params['name'] . '%');
            })
            ->when(isset($params['sku']) && $params['sku'] !== '', function (Builder $query) use ($params) {
                return $query->where('sku', 'like', '%' . $params['sku'] . '%');
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
        return DB::transaction(function () use ($params) {
            $variants = Arr::pull($params, 'variants', []);
            $specs = Arr::pull($params, 'specs', []);
            $params['sku'] = $this->generateProductSku();

            $product = $this->model->create($params);
            $this->syncVariantsAndSpecs($product, $variants, $specs);

            return $product;
        });
    }

    public function getProductByID(int $id): Product
    {
        return $this->model->withTrashed()->findOrFail($id);
    }

    public function updateProductByID(int $id, array $params): bool
    {
        return (bool) DB::transaction(function () use ($id, $params) {
            $product = $this->model->withTrashed()->findOrFail($id);

            $variants = Arr::pull($params, 'variants', null);
            $specs = Arr::pull($params, 'specs', null);
            $shouldSyncVariants = $this->hasMeaningfulVariantPayload(is_array($variants) ? $variants : []);
            $shouldSyncSpecs = $this->hasMeaningfulSpecPayload(is_array($specs) ? $specs : []);
            unset($params['sku']);

            $updated = $product->update($params);
            if (! $updated) {
                return false;
            }

            if ($shouldSyncVariants || $shouldSyncSpecs) {
                $this->syncVariantsAndSpecs($product, $variants ?? [], $specs ?? []);
            }

            return true;
        });
    }

    /**
     * Đồng bộ variants/specs của sản phẩm theo payload từ form admin.
     */
    private function syncVariantsAndSpecs(Product $product, array $variants, array $specs): void
    {
        $variantRows = collect($variants)
            ->filter(fn ($row) => is_array($row))
            ->map(function (array $row) {
                return [
                    'sku_variant' => trim((string) ($row['sku_variant'] ?? '')) ?: $this->generateVariantSku(),
                    'color_name' => trim((string) ($row['color_name'] ?? '')) ?: null,
                    'color_hex' => trim((string) ($row['color_hex'] ?? '')) ?: null,
                    'material_main' => trim((string) ($row['material_main'] ?? '')) ?: null,
                    'material_sub' => trim((string) ($row['material_sub'] ?? '')) ?: null,
                    'finish' => trim((string) ($row['finish'] ?? '')) ?: null,
                    'price' => (float) ($row['price'] ?? 0),
                    'currency' => strtoupper(trim((string) ($row['currency'] ?? 'VND'))) ?: 'VND',
                    'unit' => trim((string) ($row['unit'] ?? 'cai')) ?: 'cai',
                    'qty_per_set' => (int) ($row['qty_per_set'] ?? 1),
                    'is_default' => (bool) ($row['is_default'] ?? false),
                    'is_active' => (bool) ($row['is_active'] ?? true),
                ];
            })
            ->filter(function (array $row) {
                return $row['sku_variant'] !== null
                    || $row['color_name'] !== null
                    || $row['material_main'] !== null
                    || $row['price'] > 0;
            })
            ->values();

        if ($variantRows->isNotEmpty() && $variantRows->where('is_default', true)->count() === 0) {
            $variantRows = $variantRows->values()->map(function (array $row, int $index) {
                if ($index === 0) {
                    $row['is_default'] = true;
                }

                return $row;
            });
        }

        $specRows = collect($specs)
            ->filter(fn ($row) => is_array($row))
            ->map(function (array $row) {
                return [
                    'length_mm' => $row['length_mm'] ?? null,
                    'width_mm' => $row['width_mm'] ?? null,
                    'height_mm' => $row['height_mm'] ?? null,
                    'weight_kg' => $row['weight_kg'] ?? null,
                    'max_load_kg' => $row['max_load_kg'] ?? null,
                    'spec_key' => trim((string) ($row['spec_key'] ?? '')) ?: null,
                    'spec_value' => trim((string) ($row['spec_value'] ?? '')) ?: null,
                    'spec_unit' => trim((string) ($row['spec_unit'] ?? '')) ?: null,
                    'spec_group' => trim((string) ($row['spec_group'] ?? '')) ?: null,
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                    'created_at' => now(),
                ];
            })
            ->filter(fn (array $row) => $row['spec_key'] !== null || $row['spec_value'] !== null)
            ->values();

        ProductVariant::query()->where('product_id', $product->id)->delete();
        ProductSpec::query()->where('product_id', $product->id)->delete();

        if ($variantRows->isNotEmpty()) {
            ProductVariant::query()->insert(
                $variantRows->map(fn (array $row) => ['product_id' => $product->id, ...$row, 'created_at' => now(), 'updated_at' => now()])->all()
            );
        }

        if ($specRows->isNotEmpty()) {
            ProductSpec::query()->insert(
                $specRows->map(fn (array $row) => ['product_id' => $product->id, ...$row])->all()
            );
        }
    }

    // Kiểm tra payload variants có dữ liệu nghiệp vụ thực sự hay không
    private function hasMeaningfulVariantPayload(array $variants): bool
    {
        return collect($variants)->contains(function ($row) {
            if (! is_array($row)) {
                return false;
            }

            $price = (float) ($row['price'] ?? 0);

            return trim((string) ($row['sku_variant'] ?? '')) !== ''
                || trim((string) ($row['color_name'] ?? '')) !== ''
                || trim((string) ($row['material_main'] ?? '')) !== ''
                || $price > 0;
        });
    }

    // Kiểm tra payload specs có dữ liệu nghiệp vụ thực sự hay không
    private function hasMeaningfulSpecPayload(array $specs): bool
    {
        return collect($specs)->contains(function ($row) {
            if (! is_array($row)) {
                return false;
            }

            return trim((string) ($row['spec_key'] ?? '')) !== ''
                || trim((string) ($row['spec_value'] ?? '')) !== '';
        });
    }

    // Tạo SKU cho sản phẩm theo prefix PRD
    private function generateProductSku(): string
    {
        do {
            $sku = 'PRD'.now()->timestamp;
        } while ($this->model->withTrashed()->where('sku', $sku)->exists());

        return $sku;
    }

    // Tạo SKU cho biến thể theo prefix PV
    private function generateVariantSku(): string
    {
        do {
            $sku = 'PV'.now()->timestamp;
        } while (ProductVariant::query()->where('sku_variant', $sku)->exists());

        return $sku;
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

    public function getActiveProductsForBanner(): Collection
    {
        return $this->model
            ->query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getHomeBannerProductsBySide(): array
    {
        $items = HomeBannerProduct::query()
            ->with([
                'product' => function ($query) {
                    $query->with(['images' => function ($imageQuery) {
                        $imageQuery->orderByDesc('is_primary')->orderByDesc('id');
                    }]);
                },
            ])
            ->orderBy('side')
            ->orderBy('position')
            ->get();

        return [
            'left' => $items->where('side', 'left')->values(),
            'right' => $items->where('side', 'right')->values(),
        ];
    }

    public function updateHomeBannerProducts(array $payload): void
    {
        DB::transaction(function () use ($payload) {
            foreach (['left', 'right'] as $side) {
                $rows = $payload[$side] ?? [];
                for ($position = 1; $position <= 3; $position++) {
                    $productId = isset($rows[$position]) ? (int) $rows[$position] : 0;
                    if ($productId <= 0) {
                        HomeBannerProduct::query()
                            ->where('side', $side)
                            ->where('position', $position)
                            ->delete();
                        continue;
                    }

                    HomeBannerProduct::query()->updateOrCreate(
                        [
                            'side' => $side,
                            'position' => $position,
                        ],
                        [
                            'product_id' => $productId,
                        ]
                    );
                }
            }
        });
    }
}
