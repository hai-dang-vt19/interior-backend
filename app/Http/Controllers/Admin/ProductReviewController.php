<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Requests\AdminProductReviewUpdateRequest;
use App\Services\ProductReviewService;
use Illuminate\Http\Request;

class ProductReviewController extends BaseController
{
    public function __construct(
        private ProductReviewService $productReviewService
    ) {}

    /** Danh sách đánh giá sản phẩm */
    public function index(Request $request)
    {
        $reviews = $this->productReviewService->getProductReviews($request->all());

        return view('product_review.index', compact('reviews'));
    }

    /** Form sửa đánh giá */
    public function edit(int $id)
    {
        $review = $this->productReviewService->getProductReviewById($id);
        if (!$review) {
            abort(404);
        }

        return view('product_review.edit', compact('review'));
    }

    public function update(int $id, AdminProductReviewUpdateRequest $request)
    {
        $ok = $this->productReviewService->updateProductReview($id, $request->validated());
        if (!$ok) {
            return redirect()->route('admin.product-review.index')->with('dataError', 'Không tìm thấy đánh giá.');
        }

        return redirect()->route('admin.product-review.index')->with('dataSuccess', 'Đã cập nhật đánh giá.');
    }

    public function destroy(int $id)
    {
        $ok = $this->productReviewService->deleteProductReview($id);
        if (!$ok) {
            return redirect()->back()->with('dataError', 'Không tìm thấy đánh giá.');
        }

        return redirect()->back()->with('dataSuccess', 'Đã xóa đánh giá.');
    }
}
