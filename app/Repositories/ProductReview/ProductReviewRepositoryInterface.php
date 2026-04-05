<?php

declare(strict_types=1);

namespace App\Repositories\ProductReview;

use App\Models\ProductReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductReviewRepositoryInterface
{
    /** Danh sách đánh giá có phân trang và lọc theo sản phẩm */
    public function paginateProductReviews(array $params): LengthAwarePaginator;

    public function findById(int $id): ?ProductReview;

    /** Cập nhật nội dung / sao (quản trị) */
    public function updateReview(int $id, array $payload): bool;

    /** Xóa mềm đánh giá */
    public function deleteReview(int $id): bool;
}
