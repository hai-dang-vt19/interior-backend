<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProductReview;
use App\Repositories\ProductReview\ProductReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductReviewService extends BaseService
{
    public function __construct(
        private ProductReviewRepositoryInterface $productReviewRepository
    ) {}

    /** Danh sách đánh giá trong admin */
    public function getProductReviews(array $params): LengthAwarePaginator
    {
        return $this->productReviewRepository->paginateProductReviews($params);
    }

    public function getProductReviewById(int $id): ?ProductReview
    {
        return $this->productReviewRepository->findById($id);
    }

    public function updateProductReview(int $id, array $payload): bool
    {
        return $this->productReviewRepository->updateReview($id, $payload);
    }

    public function deleteProductReview(int $id): bool
    {
        return $this->productReviewRepository->deleteReview($id);
    }
}
