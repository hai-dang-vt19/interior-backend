<?php

declare(strict_types=1);

namespace App\Repositories\Staff;

use App\Enums\PerPage;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class StaffRepository implements StaffRepositoryInterface
{
    public function __construct(
        private User $model
    ) {}

    public function getStaffs(array $params): LengthAwarePaginator
    {
        $staffs = $this->model
            ->withTrashed()
            ->where('role', UserRole::STAFF->value)
            ->when(isset($params['keyword']) && $params['keyword'] !== '', function (Builder $query) use ($params) {
                return $query->where(function (Builder $q) use ($params) {
                    $q->where('full_name', 'like', '%' . $params['keyword'] . '%')
                        ->orWhere('email', 'like', '%' . $params['keyword'] . '%')
                        ->orWhere('phone', 'like', '%' . $params['keyword'] . '%')
                        ->orWhere('username', 'like', '%' . $params['keyword'] . '%');
                });
            })
            ->when(($params['deleted'] ?? 'active') === 'active', function (Builder $query) {
                return $query->whereNull('deleted_at');
            })
            ->when(($params['deleted'] ?? 'active') === 'trashed', function (Builder $query) {
                return $query->onlyTrashed();
            });

        return $staffs->orderByDesc('id')
            ->paginate($params['per_page'] ?? PerPage::PER_PAGE_10->value)
            ->withQueryString();
    }

    public function createStaff(array $params): User
    {
        return $this->model->create($params);
    }

    public function getStaffByID(int $id): User
    {
        return $this->model->withTrashed()
            ->where('role', UserRole::STAFF->value)
            ->findOrFail($id);
    }

    public function updateStaffByID(int $id, array $params): bool
    {
        return $this->model->withTrashed()
            ->where('role', UserRole::STAFF->value)
            ->findOrFail($id)
            ->update($params);
    }

    public function destroy(int $id): void
    {
        $this->model->where('role', UserRole::STAFF->value)->findOrFail($id)->delete();
    }

    public function restore(int $id): bool
    {
        return (bool) $this->model->withTrashed()
            ->where('role', UserRole::STAFF->value)
            ->findOrFail($id)
            ->restore();
    }

    public function forceDelete(int $id): bool
    {
        return (bool) $this->model->withTrashed()
            ->where('role', UserRole::STAFF->value)
            ->findOrFail($id)
            ->forceDelete();
    }
}
