<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\AdminAuth\AdminAuthRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class AdminAuthService extends BaseService
{
    public function __construct(
        private AdminAuthRepositoryInterface $adminAuthRepository
    ) {}

    public function registerStaff(array $payload, string $ipAddress, string $userAgent): User
    {
        $user = $this->adminAuthRepository->createStaffUser([
            'full_name' => $payload['full_name'],
            'phone' => $payload['phone'],
            'email' => $payload['email'] ?? null,
            'password' => Hash::make($payload['password']),
            'role' => UserRole::STAFF,
        ]);

        $this->adminAuthRepository->writeAuthActivityLog(
            (int) $user->id,
            'register',
            'Đăng ký tài khoản staff',
            $ipAddress,
            $userAgent
        );

        return $user;
    }

    public function attemptLogin(string $phone, string $password, string $ipAddress, string $userAgent): array
    {
        $user = $this->adminAuthRepository->findUserByPhone($phone);
        if (!$user || !Hash::check($password, (string) $user->password)) {
            return [
                'ok' => false,
                'code' => 401,
                'message' => 'Số điện thoại hoặc mật khẩu không đúng',
            ];
        }

        if (!in_array($user->role->value, [UserRole::ADMIN->value, UserRole::STAFF->value], true)) {
            return [
                'ok' => false,
                'code' => 403,
                'message' => 'Bạn không có quyền truy cập vào trang quản trị',
            ];
        }

        $this->adminAuthRepository->writeAuthActivityLog(
            (int) $user->id,
            'login',
            'Đăng nhập hệ thống',
            $ipAddress,
            $userAgent
        );

        return [
            'ok' => true,
            'user' => $user,
        ];
    }

    public function writeLogoutLog(int $userId, string $ipAddress, string $userAgent): void
    {
        $this->adminAuthRepository->writeAuthActivityLog(
            $userId,
            'logout',
            'Đăng xuất hệ thống',
            $ipAddress,
            $userAgent
        );
    }

    public function getActivityLogs(string $keyword): LengthAwarePaginator
    {
        return $this->adminAuthRepository->getActivityLogs($keyword);
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword, string $ipAddress, string $userAgent): bool
    {
        if (!Hash::check($currentPassword, (string) $user->password)) {
            return false;
        }

        $this->adminAuthRepository->updateUserPassword((int) $user->id, Hash::make($newPassword));
        $this->adminAuthRepository->writeAuthActivityLog(
            (int) $user->id,
            'change_password',
            'Đổi mật khẩu tài khoản',
            $ipAddress,
            $userAgent
        );

        return true;
    }
}
