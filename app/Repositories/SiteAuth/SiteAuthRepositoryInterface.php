<?php

declare(strict_types=1);

namespace App\Repositories\SiteAuth;

use App\Models\Customer;

interface SiteAuthRepositoryInterface
{
    public function findCustomerByEmail(string $email): ?Customer;
    public function createCustomer(array $payload): Customer;
}
