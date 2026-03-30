<?php

declare(strict_types=1);

namespace App\Repositories\AdminAuth;

use App\Models\AuthActivityLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminAuthRepository implements AdminAuthRepositoryInterface
{
    public function __construct(
        private User $userModel,
        private AuthActivityLog $authActivityLogModel
    ) {}

    public function createStaffUser(array $payload): User
    {
        return $this->userModel->query()->create($payload);
    }

    public function findUserByPhone(string $phone): ?User
    {
        return $this->userModel->query()->where('phone', $phone)->first();
    }

    public function writeAuthActivityLog(int $userId, string $action, string $description, string $ipAddress, string $userAgent): void
    {
        $this->authActivityLogModel->query()->create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    public function getActivityLogs(string $keyword): LengthAwarePaginator
    {
        return $this->authActivityLogModel->query()
            ->with('user')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where('action', 'like', '%'.$keyword.'%')
                    ->orWhere('description', 'like', '%'.$keyword.'%');
            })
            ->latest('id')
            ->paginate(20)
            ->appends(['keyword' => $keyword]);
    }

    public function updateUserPassword(int $userId, string $hashedPassword): bool
    {
        return $this->userModel->query()->where('id', $userId)->update(['password' => $hashedPassword]) > 0;
    }
}
