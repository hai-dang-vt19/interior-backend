<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\SiteProductReviewRequest;
use App\Services\SiteService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SiteProductReviewController extends Controller
{
    public function __construct(
        private SiteService $siteService
    ) {}

    public function store(SiteProductReviewRequest $request, int $productId)
    {
        $customerId = (int) auth()->guard('customer')->id();

        try {
            $this->siteService->getProductDetailData($productId, $customerId);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        $created = $this->siteService->storeProductReview($customerId, $productId, $request->validated());
        if ($created === null) {
            return redirect()
                ->route('site.products.show', $productId)
                ->withFragment('product-reviews')
                ->with('dataError', 'Bạn không thể gửi đánh giá (chưa mua thành công hoặc đã có đánh giá cho sản phẩm này).');
        }

        return redirect()
            ->route('site.products.show', $productId)
            ->withFragment('product-reviews')
            ->with('dataSuccess', 'Cảm ơn bạn đã đánh giá sản phẩm.');
    }

    public function update(SiteProductReviewRequest $request, int $productId, int $reviewId)
    {
        $customerId = (int) auth()->guard('customer')->id();

        try {
            $this->siteService->getProductDetailData($productId, $customerId);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        $ok = $this->siteService->updateProductReview($customerId, $productId, $reviewId, $request->validated());
        if (!$ok) {
            return redirect()
                ->route('site.products.show', $productId)
                ->withFragment('product-reviews')
                ->with('dataError', 'Không tìm thấy đánh giá để cập nhật.');
        }

        return redirect()
            ->route('site.products.show', $productId)
            ->withFragment('product-reviews')
            ->with('dataSuccess', 'Đã cập nhật đánh giá.');
    }
}
