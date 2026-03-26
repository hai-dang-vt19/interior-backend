<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Staff\StaffRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class StaffService extends BaseService
{
    public function __construct(
        private StaffRepositoryInterface $staffRepository
    ) {}

    public function getStaffs(array $params): LengthAwarePaginator
    {
        return $this->staffRepository->getStaffs($params);
    }

    // Tạo mới tài khoản nhân viên
    public function createStaff(array $params): User
    {
        $params['role'] = UserRole::STAFF->value;
        $params['name'] = $params['username'];
        $params['password'] = Hash::make($params['password']);

        return $this->staffRepository->createStaff($params);
    }

    public function getStaffByID(int $id): User
    {
        return $this->staffRepository->getStaffByID($id);
    }

    // Cập nhật thông tin nhân viên, giữ mật khẩu cũ nếu để trống
    public function updateStaffByID(int $id, array $params): bool
    {
        $params['name'] = $params['username'];
        if (!empty($params['password'])) {
            $params['password'] = Hash::make($params['password']);
        } else {
            unset($params['password']);
        }

        return $this->staffRepository->updateStaffByID($id, $params);
    }

    public function destroy(int $id): void
    {
        $this->staffRepository->destroy($id);
    }

    public function restore(int $id): bool
    {
        return $this->staffRepository->restore($id);
    }

    public function forceDelete(int $id): bool
    {
        return $this->staffRepository->forceDelete($id);
    }
}
