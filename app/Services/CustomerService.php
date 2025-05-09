<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CustomerStatus;
use App\Repositories\Customer\CustomerRepositoryInterface;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class CustomerService extends BaseService
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository
    ) {}

    public function getCustomers(array $params) : LengthAwarePaginator
    {
        return $this->customerRepository->getCustomers($params);
    }

    public function updateCustomerByID(int $id, array $params)
    {
        if (isset($params['status'])) {
            if ($params['status'] == CustomerStatus::ACTIVE->value) {
                $params['deleted_at'] = null;
            } else {
                $params['deleted_at'] = now();
            }

            unset($params['status']);
        }
        
        try {
            $result = $this->customerRepository->updateCustomerByID($id, $params);
            return $result;
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
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
