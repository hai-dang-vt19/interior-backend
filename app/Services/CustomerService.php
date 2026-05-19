<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CustomerStatus;
use App\Mail\CustomerCreatedByAdminMail;
use App\Models\Customer;
use App\Repositories\Customer\CustomerRepositoryInterface;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CustomerService extends BaseService
{
    public const DEFAULT_PASSWORD = '12345678';

    public function __construct(
        private CustomerRepositoryInterface $customerRepository
    ) {}

    public function getCustomers(array $params) : LengthAwarePaginator
    {
        return $this->customerRepository->getCustomers($params);
    }

    // Chuẩn hóa dữ liệu và tạo mới khách hàng
    public function createCustomer(array $params): Customer
    {
        $plainPassword = self::DEFAULT_PASSWORD;
        $params['password'] = Hash::make($plainPassword);
        $params['loyalty_tier'] = $params['loyalty_tier'] ?? 'standard';
        $params['reward_points'] = $params['reward_points'] ?? 0;
        $params['email_verified_at'] = now();
        $params['email_verification_token_hash'] = null;
        $params['email_verification_token_expires_at'] = null;
        if (($params['status'] ?? null) == CustomerStatus::INACTIVE->value) {
            $params['deleted_at'] = now();
        }
        unset($params['status']);

        $customer = $this->customerRepository->createCustomer($params);
        $this->sendCreatedByAdminNotification($customer, $plainPassword);

        return $customer;
    }

    /** Gửi email thông báo tài khoản mới (không chặn luồng tạo khách nếu SMTP lỗi). */
    private function sendCreatedByAdminNotification(Customer $customer, string $plainPassword): void
    {
        $email = trim((string) $customer->email);
        if ($email === '') {
            return;
        }

        try {
            Mail::to($email)->send(new CustomerCreatedByAdminMail(
                $customer,
                $plainPassword,
                route('site.login')
            ));
        } catch (\Throwable $e) {
            Log::warning('Gửi mail thông báo tạo khách hàng không thành công', [
                'customer_id' => $customer->id,
                'email' => $email,
                'message' => $e->getMessage(),
            ]);
        }
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

    public function restore(int $id): bool
    {
        return $this->customerRepository->restore($id);
    }

    public function forceDelete(int $id): bool
    {
        return $this->customerRepository->forceDelete($id);
    }

    public function getCustomerProfile(int $id): Customer
    {
        return $this->customerRepository->getCustomerProfile($id);
    }

    public function addAddress(int $customerId, array $params): bool
    {
        return $this->customerRepository->addAddress($customerId, $params);
    }

    public function deleteAddress(int $customerId, int $addressId): bool
    {
        return $this->customerRepository->deleteAddress($customerId, $addressId);
    }

    public function addContactLog(int $customerId, array $params): bool
    {
        return $this->customerRepository->addContactLog($customerId, $params);
    }

}
