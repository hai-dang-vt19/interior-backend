<?php

declare(strict_types=1);

namespace App\Repositories\SiteAuth;

use App\Models\Customer;

class SiteAuthRepository implements SiteAuthRepositoryInterface
{
    public function __construct(
        private Customer $customerModel
    ) {}

    public function findCustomerByEmail(string $email): ?Customer
    {
        return $this->customerModel->query()->where('email', $email)->first();
    }

    public function createCustomer(array $payload): Customer
    {
        return $this->customerModel->query()->create($payload);
    }
}
