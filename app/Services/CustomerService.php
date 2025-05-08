<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Customer\CustomerRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerService extends BaseService
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository
    ) {}

    public function getCustomers(array $params) : LengthAwarePaginator
    {
        return $this->customerRepository->getCustomers($params);
    }

    public function getCustomerByID(int $id) : Customer
    {
        return $this->customerRepository->getCustomerByID($id);
    }

    public function destroy(int $id) : void
    {
        $this->customerRepository->destroy($id);
    }

}
