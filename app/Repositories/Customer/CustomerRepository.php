<?php

declare(strict_types=1);

namespace App\Repositories\Customer;

use App\Enums\CustomerStatus;
use App\Enums\PerPage;
use App\Models\CustomerAddress;
use App\Models\CustomerContactLog;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function __construct(
        private Customer $model
    ) {}

    public function getCustomers(array $params): LengthAwarePaginator
    {
        $customers = $this->model
            ->withTrashed()
            ->when(isset($params['full_name']), function (Builder $query) use ($params) {
                return $query->where('full_name', 'like', '%' . $params['full_name'] . '%');
            })
            ->when(isset($params['email']), function (Builder $query) use ($params) {
                return $query->where('email', $params['email']);
            })
            ->when(isset($params['phone']), function (Builder $query) use ($params) {
                return $query->where('phone', $params['phone']);
            })
            ->when(isset($params['loyalty_tier']) && $params['loyalty_tier'] !== '', function (Builder $query) use ($params) {
                return $query->where('loyalty_tier', $params['loyalty_tier']);
            })
            ->when(isset($params['status']), function (Builder $query) use ($params) {
                if ($params['status'] == CustomerStatus::INACTIVE->value) {
                    return $query->onlyTrashed();
                }
                if ($params['status'] == CustomerStatus::ACTIVE->value) {
                    return $query->whereNull('deleted_at');
                }
            })
            ->when(isset($params['dateFrom']), function (Builder $query) use ($params) {
                $dates = explode(' - ', $params['dateFrom']);
                if (count($dates) === 2) {
                    return $query->whereBetween('created_at', [
                        Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay(),
                        Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay()
                    ]);
                }
                $date = Carbon::createFromFormat('d/m/Y', $params['dateFrom']);
                return $query->whereDate('created_at', $date->format('Y-m-d'));
            })
            ->when(($params['deleted'] ?? 'active') === 'active', function (Builder $query) {
                return $query->whereNull('deleted_at');
            })
            ->when(($params['deleted'] ?? 'active') === 'trashed', function (Builder $query) {
                return $query->onlyTrashed();
            });

        return $customers->paginate(isset($params['per_page']) ? $params['per_page'] : PerPage::PER_PAGE_10->value)
            ->withQueryString();
    }

    public function createCustomer(array $params): Customer
    {
        return $this->model->create($params);
    }

    public function updateCustomerByID(int $id, array $params): bool
    {
        return $this->model->withTrashed()->findOrFail($id)->update($params);
    }

    public function getCustomerByID(int $id): Customer
    {
        return $this->model->findOrFail($id);
    }

    public function destroy(int $id): void
    {
        $this->model->findOrFail($id)->delete();
    }

    public function restore(int $id): bool
    {
        return (bool) $this->model->withTrashed()->findOrFail($id)->restore();
    }

    public function forceDelete(int $id): bool
    {
        return (bool) $this->model->withTrashed()->findOrFail($id)->forceDelete();
    }

    public function getCustomerProfile(int $id): Customer
    {
        return $this->model->withTrashed()
            ->with([
                'addresses' => function ($query) {
                    $query->orderByDesc('is_default')->orderByDesc('id');
                },
                'contactLogs' => function ($query) {
                    $query->with('contactedBy')->orderByDesc('id');
                },
            ])
            ->findOrFail($id);
    }

    public function addAddress(int $customerId, array $params): bool
    {
        if (!empty($params['is_default'])) {
            CustomerAddress::query()->where('customer_id', $customerId)->update(['is_default' => false]);
        }

        return (bool) CustomerAddress::query()->create([
            'customer_id' => $customerId,
            'address_line' => $params['address_line'],
            'city' => $params['city'] ?? null,
            'district' => $params['district'] ?? null,
            'ward' => $params['ward'] ?? null,
            'postal_code' => $params['postal_code'] ?? null,
            'is_default' => (bool) ($params['is_default'] ?? false),
        ]);
    }

    public function deleteAddress(int $customerId, int $addressId): bool
    {
        return (bool) CustomerAddress::query()
            ->where('customer_id', $customerId)
            ->where('id', $addressId)
            ->delete();
    }

    public function addContactLog(int $customerId, array $params): bool
    {
        return (bool) CustomerContactLog::query()->create([
            'customer_id' => $customerId,
            'channel' => $params['channel'],
            'title' => $params['title'] ?? null,
            'message' => $params['message'],
            'contacted_by' => $params['contacted_by'] ?? null,
        ]);
    }
}
