<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Repositories\Product\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductService extends BaseService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    // Lấy danh sách sản phẩm cho trang quản trị
    public function getProducts(array $params): LengthAwarePaginator
    {
        return $this->productRepository->getProducts($params);
    }

    // Lấy danh mục để hiển thị combobox
    public function getCategories(): Collection
    {
        return $this->productRepository->getCategories();
    }

    // Tạo mới sản phẩm
    public function createProduct(array $params): Product
    {
        return $this->productRepository->createProduct($params);
    }

    // Lấy chi tiết sản phẩm theo id
    public function getProductByID(int $id): Product
    {
        return $this->productRepository->getProductByID($id);
    }

    // Cập nhật thông tin sản phẩm
    public function updateProductByID(int $id, array $params): bool
    {
        return $this->productRepository->updateProductByID($id, $params);
    }

    // Xóa mềm sản phẩm
    public function destroy(int $id): void
    {
        $this->productRepository->destroy($id);
    }

    // Khôi phục sản phẩm từ thùng rác
    public function restore(int $id): bool
    {
        return $this->productRepository->restore($id);
    }

    // Xóa vĩnh viễn sản phẩm đã xóa mềm
    public function forceDelete(int $id): bool
    {
        return $this->productRepository->forceDelete($id);
    }

    // Lấy sản phẩm kèm danh sách ảnh
    public function getProductImages(int $productId): Product
    {
        return $this->productRepository->getProductImages($productId);
    }

    // Thêm ảnh phụ cho sản phẩm
    public function addProductImage(int $productId, array $params): ProductImage
    {
        return $this->productRepository->addProductImage($productId, $params);
    }

    // Xóa ảnh phụ của sản phẩm
    public function deleteProductImage(int $productId, int $imageId): bool
    {
        return $this->productRepository->deleteProductImage($productId, $imageId);
    }

    // Lấy thông tin tồn kho và lịch sử thay đổi
    public function getProductInventory(int $productId): Product
    {
        return $this->productRepository->getProductInventory($productId);
    }

    // Điều chỉnh tồn kho và ghi lịch sử
    public function adjustInventory(int $productId, array $params, int $createdBy): bool
    {
        return $this->productRepository->adjustInventory($productId, $params, $createdBy);
    }
}
