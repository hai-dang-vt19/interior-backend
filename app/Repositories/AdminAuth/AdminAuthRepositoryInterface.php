<?php

declare(strict_types=1);

namespace App\Repositories\AdminAuth;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdminAuthRepositoryInterface
{
    public function createStaffUser(array $payload): User;
    public function findUserByPhone(string $phone): ?User;
    public function writeAuthActivityLog(int $userId, string $action, string $description, string $ipAddress, string $userAgent): void;
    public function getActivityLogs(string $keyword): LengthAwarePaginator;
    public function updateUserPassword(int $userId, string $hashedPassword): bool;
}
