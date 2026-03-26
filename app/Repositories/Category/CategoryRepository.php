<?php

declare(strict_types=1);

namespace App\Repositories\Category;

use App\Enums\PerPage;
use App\Models\Category;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        private Category $model
    ) {}

    public function getCategories(array $params): LengthAwarePaginator
    {
        $categories = $this->model
            ->withTrashed()
            ->with('parent')
            ->when(isset($params['name']) && $params['name'] !== '', function (Builder $query) use ($params) {
                return $query->where('name', 'like', '%' . $params['name'] . '%');
            })
            ->when(($params['deleted'] ?? 'active') === 'active', function (Builder $query) {
                return $query->whereNull('deleted_at');
            })
            ->when(($params['deleted'] ?? 'active') === 'trashed', function (Builder $query) {
                return $query->onlyTrashed();
            });

        return $categories->orderByDesc('id')
            ->paginate($params['per_page'] ?? PerPage::PER_PAGE_10->value)
            ->withQueryString();
    }

    public function getParentCategories(): Collection
    {
        return $this->model->query()->whereNull('parent_id')->orderBy('name')->get();
    }

    public function createCategory(array $params): Category
    {
        return $this->model->create($params);
    }

    public function getCategoryByID(int $id): Category
    {
        return $this->model->withTrashed()->findOrFail($id);
    }

    public function updateCategoryByID(int $id, array $params): bool
    {
        return $this->model->withTrashed()->findOrFail($id)->update($params);
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
        return (bool) $this->model->withTrashed()->findOrFail($id)->forceDelete();
    }
}
