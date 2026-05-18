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

    public function findCustomerByEmail(string $email): ?Customer
    {
        return $this->customerModel->query()
            ->whereRaw('LOWER(email) = ?', [mb_strtolower(trim($email))])
            ->first();
    }

    public function updateCustomerPassword(Customer $customer, string $plainPassword): void
    {
        $customer->forceFill(['password' => $plainPassword])->save();
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
