<?php

declare(strict_types=1);

namespace App\Repositories\Category;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    public function getCategories(array $params): LengthAwarePaginator;
    public function getParentCategories(): Collection;
    public function createCategory(array $params): Category;
    public function getCategoryByID(int $id): Category;
    public function updateCategoryByID(int $id, array $params): bool;
    public function destroy(int $id): void;
    public function restore(int $id): bool;
    public function forceDelete(int $id): bool;
}
