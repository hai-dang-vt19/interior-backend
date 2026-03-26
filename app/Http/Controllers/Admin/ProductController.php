<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductEditRequest;
use App\Http\Requests\ProductImageRequest;
use App\Http\Requests\ProductInventoryRequest;
use App\Http\Requests\ProductRequest;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends BaseController
{
    public function __construct(
        private ProductService $productService
    ) {}

    // Hiển thị danh sách và bộ lọc sản phẩm
    public function index(Request $request)
    {
        $products = $this->productService->getProducts($request->all());
        $categories = $this->productService->getCategories();

        return view('product.index', compact('products', 'categories'));
    }

    // Tạo mới sản phẩm
    public function store(ProductRequest $request)
    {
        $this->productService->createProduct($request->validated());

        return redirect()->back()->with('dataSuccess', 'Tạo mới sản phẩm thành công');
    }

    // Lấy thông tin sản phẩm theo id
    public function edit(int $id)
    {
        return $this->productService->getProductByID($id);
    }

    // Cập nhật thông tin sản phẩm
    public function update(int $id, ProductEditRequest $request)
    {
        $this->productService->updateProductByID($id, $request->validated());

        return redirect()->back()->with('dataSuccess', 'Cập nhật sản phẩm thành công');
    }

    // Xóa mềm sản phẩm
    public function destroy(int $id)
    {
        $this->productService->destroy($id);

        return redirect()->back()->with('dataSuccess', 'Xóa sản phẩm thành công');
    }

    // Khôi phục sản phẩm đã xóa mềm
    public function restore(int $id)
    {
        $this->productService->restore($id);

        return redirect()->back()->with('dataSuccess', 'Khôi phục sản phẩm thành công');
    }

    // Xóa vĩnh viễn sản phẩm
    public function forceDelete(int $id)
    {
        $this->productService->forceDelete($id);

        return redirect()->back()->with('dataSuccess', 'Xóa vĩnh viễn sản phẩm thành công');
    }

    // Màn hình quản lý ảnh của sản phẩm
    public function images(int $id)
    {
        $product = $this->productService->getProductImages($id);

        return view('product.images', compact('product'));
    }

    // Thêm ảnh phụ theo URL
    public function storeImage(int $id, ProductImageRequest $request)
    {
        $this->productService->addProductImage($id, $request->validated());

        return redirect()->back()->with('dataSuccess', 'Thêm ảnh thành công');
    }

    // Xóa ảnh phụ
    public function destroyImage(int $id, int $imageId)
    {
        $this->productService->deleteProductImage($id, $imageId);

        return redirect()->back()->with('dataSuccess', 'Xóa ảnh thành công');
    }

    // Màn hình tồn kho và lịch sử thay đổi
    public function inventory(int $id)
    {
        $product = $this->productService->getProductInventory($id);

        return view('product.inventory', compact('product'));
    }

    // Điều chỉnh tồn kho (nhập/xuất/đặt lại)
    public function adjustInventory(int $id, ProductInventoryRequest $request)
    {
        $this->productService->adjustInventory($id, $request->validated(), (int) Auth::id());

        return redirect()->back()->with('dataSuccess', 'Cập nhật tồn kho thành công');
    }
}
