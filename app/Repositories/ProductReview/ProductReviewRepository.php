<?php

declare(strict_types=1);

namespace App\Repositories\ProductReview;

use App\Enums\PerPage;
use App\Models\ProductReview;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductReviewRepository implements ProductReviewRepositoryInterface
{
    public function __construct(
        private ProductReview $model
    ) {}

    public function paginateProductReviews(array $params): LengthAwarePaginator
    {
        $perPage = (int) ($params['per_page'] ?? PerPage::PER_PAGE_20->value);
        if (!in_array($perPage, array_column(PerPage::cases(), 'value'), true)) {
            $perPage = PerPage::PER_PAGE_20->value;
        }

        return $this->model->query()
            ->with(['product:id,name', 'customer:id,full_name,email'])
            ->when(isset($params['product_id']) && $params['product_id'] !== '', function (Builder $query) use ($params) {
                $query->where('product_id', (int) $params['product_id']);
            })
            ->when(isset($params['keyword']) && trim((string) $params['keyword']) !== '', function (Builder $query) use ($params) {
                $kw = '%' . trim((string) $params['keyword']) . '%';
                $query->where(function (Builder $q) use ($kw) {
                    $q->where('review', 'like', $kw)
                        ->orWhereHas('customer', fn (Builder $cq) => $cq->where('full_name', 'like', $kw)->orWhere('email', 'like', $kw))
                        ->orWhereHas('product', fn (Builder $pq) => $pq->where('name', 'like', $kw));
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->appends($params);
    }

    public function findById(int $id): ?ProductReview
    {
        return $this->model->query()
            ->with(['product:id,name', 'customer:id,full_name,email'])
            ->where('id', $id)
            ->first();
    }

    public function updateReview(int $id, array $payload): bool
    {
        $review = $this->model->query()->where('id', $id)->first();
        if (!$review) {
            return false;
        }

        return $review->update([
            'review' => $payload['review'],
            'rating' => (int) $payload['rating'],
        ]);
    }

    public function deleteReview(int $id): bool
    {
        $review = $this->model->query()->where('id', $id)->first();
        if (!$review) {
            return false;
        }

        return (bool) $review->delete();
    }
}
