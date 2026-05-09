<?php

declare(strict_types=1);

namespace App\Repositories\SiteAuth;

use App\Models\Customer;

class SiteAuthRepository implements SiteAuthRepositoryInterface
{
    public function __construct(
        private Customer $customerModel
    ) {}

    public function findCustomerByPhone(string $phone): ?Customer
    {
        return $this->customerModel->query()->where('phone', $phone)->first();
    }

    public function findCustomerByEmailVerificationTokenHash(string $tokenHash): ?Customer
    {
        return $this->customerModel->query()
            ->where('email_verification_token_hash', $tokenHash)
            ->first();
    }

    public function createCustomer(array $payload): Customer
    {
        return $this->customerModel->query()->create($payload);
    }

    public function markCustomerEmailVerified(Customer $customer): void
    {
        $customer->forceFill([
            'email_verified_at' => now(),
            'email_verification_token_hash' => null,
            'email_verification_token_expires_at' => null,
        ])->save();
    }
}
