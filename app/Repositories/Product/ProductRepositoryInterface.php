<?php

declare(strict_types=1);

namespace App\Repositories\Product;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    public function getProducts(array $params): LengthAwarePaginator;
    public function getCategories(): Collection;
    public function createProduct(array $params): Product;
    public function getProductByID(int $id): Product;
    public function updateProductByID(int $id, array $params): bool;
    public function destroy(int $id): void;
    public function restore(int $id): bool;
    public function forceDelete(int $id): bool;
    public function getProductImages(int $productId): Product;
    public function addProductImage(int $productId, array $params): ProductImage;
    public function setPrimaryProductImage(int $productId, int $imageId): bool;
    public function deleteProductImage(int $productId, int $imageId): bool;
    public function getProductInventory(int $productId): Product;
    public function adjustInventory(int $productId, array $params, int $createdBy): bool;
}
