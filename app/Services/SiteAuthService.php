<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Repositories\SiteAuth\SiteAuthRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class SiteAuthService extends BaseService
{
    public function __construct(
        private SiteAuthRepositoryInterface $siteAuthRepository
    ) {}

    public function attemptLogin(string $email, string $password): ?Customer
    {
        $customer = $this->siteAuthRepository->findCustomerByEmail($email);
        if (!$customer || !Hash::check($password, $customer->password)) {
            return null;
        }

        return $customer;
    }

    public function registerCustomer(array $payload): Customer
    {
        return $this->siteAuthRepository->createCustomer([
            'full_name' => $payload['full_name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'] ?? null,
            'password' => Hash::make($payload['password']),
            'loyalty_tier' => 'standard',
            'reward_points' => 0,
        ]);
    }
}
