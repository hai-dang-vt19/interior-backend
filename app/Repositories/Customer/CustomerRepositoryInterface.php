<?php

declare(strict_types=1);

namespace App\Repositories\Customer;

use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface
{
    public function getCustomers(array $params) : LengthAwarePaginator;
    public function getCustomerByID(int $id) : Customer;
    public function destroy(int $id) : void;
}