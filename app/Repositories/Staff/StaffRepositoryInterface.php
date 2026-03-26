<?php

declare(strict_types=1);

namespace App\Repositories\Staff;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface StaffRepositoryInterface
{
    public function getStaffs(array $params): LengthAwarePaginator;
    public function createStaff(array $params): User;
    public function getStaffByID(int $id): User;
    public function updateStaffByID(int $id, array $params): bool;
    public function destroy(int $id): void;
    public function restore(int $id): bool;
    public function forceDelete(int $id): bool;
}
