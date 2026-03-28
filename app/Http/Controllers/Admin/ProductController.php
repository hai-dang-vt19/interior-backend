<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductEditRequest;
use App\Http\Requests\ProductImageRequest;
use App\Http\Requests\ProductInventoryRequest;
use App\Http\Requests\ProductRequest;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

    // Thêm một hoặc nhiều ảnh: storage/public/product/{product_id}/{timestamp}_{tên}.{ext}
    public function storeImage(int $id, ProductImageRequest $request)
    {
        $files = $request->file('images', []);
        $count = 0;
        foreach ($files as $index => $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }
            $this->storeOneProductImageFile($id, $file, $index);
            $count++;
        }

        if ($count === 0) {
            return redirect()->back()->with('dataError', 'Không có file hợp lệ để tải lên');
        }

        $message = $count === 1 ? 'Đã tải lên 1 ảnh' : "Đã tải lên {$count} ảnh";

        return redirect()->back()->with('dataSuccess', $message);
    }

    // Đặt ảnh primary (đại diện) trong admin
    public function setPrimaryImage(int $id, int $imageId)
    {
        $ok = $this->productService->setPrimaryProductImage($id, $imageId);

        return $ok
            ? redirect()->back()->with('dataSuccess', 'Đã đặt làm ảnh chính')
            : redirect()->back()->with('dataError', 'Không thành công');
    }

    /**
     * Lưu một file upload vào disk public và tạo bản ghi product_images.
     */
    private function storeOneProductImageFile(int $productId, UploadedFile $file, int $uploadIndex = 0): void
    {
        $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg');
        $slug = Str::slug($base, '_');
        if ($slug === '') {
            $slug = 'image';
        }
        $ts = (int) (microtime(true) * 1000);
        $filename = $ts.'_'.$uploadIndex.'_'.$slug.'.'.$ext;
        $relativePath = $file->storeAs('product/'.$productId, $filename, 'public');
        $this->productService->addProductImage($productId, ['image_url' => $relativePath]);
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
