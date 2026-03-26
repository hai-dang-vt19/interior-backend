<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Repositories\Category\CategoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CategoryService extends BaseService
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function getCategories(array $params): LengthAwarePaginator
    {
        return $this->categoryRepository->getCategories($params);
    }

    public function getParentCategories(): Collection
    {
        return $this->categoryRepository->getParentCategories();
    }

    public function createCategory(array $params): Category
    {
        return $this->categoryRepository->createCategory($params);
    }

    public function getCategoryByID(int $id): Category
    {
        return $this->categoryRepository->getCategoryByID($id);
    }

    public function updateCategoryByID(int $id, array $params): bool
    {
        return $this->categoryRepository->updateCategoryByID($id, $params);
    }

    public function destroy(int $id): void
    {
        $this->categoryRepository->destroy($id);
    }

    public function restore(int $id): bool
    {
        return $this->categoryRepository->restore($id);
    }

    public function forceDelete(int $id): bool
    {
        return $this->categoryRepository->forceDelete($id);
    }
}
