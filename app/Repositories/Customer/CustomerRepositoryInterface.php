<?php

declare(strict_types=1);

namespace App\Repositories\Customer;

use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface
{
    public function getCustomers(array $params) : LengthAwarePaginator;
    public function createCustomer(array $params) : Customer;
    public function getCustomerByID(int $id) : Customer;
    public function updateCustomerByID(int $id, array $params) :bool;
    public function destroy(int $id) : void;
    public function restore(int $id) : bool;
    public function forceDelete(int $id) : bool;
    public function getCustomerProfile(int $id) : Customer;
    public function addAddress(int $customerId, array $params) : bool;
    public function deleteAddress(int $customerId, int $addressId) : bool;
    public function addContactLog(int $customerId, array $params) : bool;
}