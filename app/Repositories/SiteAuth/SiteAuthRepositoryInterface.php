<?php

declare(strict_types=1);

namespace App\Repositories\SiteAuth;

use App\Models\Customer;

interface SiteAuthRepositoryInterface
{
    public function findCustomerByPhone(string $phone): ?Customer;

    /** Tìm khách theo SHA-256 của token xác thực gửi qua email */
    public function findCustomerByEmailVerificationTokenHash(string $tokenHash): ?Customer;

    public function createCustomer(array $payload): Customer;

    /** Đánh dấu email đã xác thực và xóa token dùng một lần */
    public function markCustomerEmailVerified(Customer $customer): void;
}
