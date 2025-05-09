<?php

declare(strict_types=1);

namespace App\Repositories\Customer;

use App\Enums\CustomerStatus;
use App\Enums\PerPage;
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
            ->when(isset($params['full_name']), function (Builder $query) use ($params) {
                return $query->where('full_name', 'like', '%' . $params['full_name'] . '%');
            })
            ->when(isset($params['email']), function (Builder $query) use ($params) {
                return $query->where('email', $params['email']);
            })
            ->when(isset($params['phone']), function (Builder $query) use ($params) {
                return $query->where('phone', $params['phone']);
            })
            ->when(isset($params['status']), function (Builder $query) use ($params) {
                if ($params['status'] == CustomerStatus::INACTIVE->value) {
                    return $query->onlyTrashed();
                }
            })
            ->when(isset($params['dateFrom']), function (Builder $query) use ($params) {
                $explodeDate = explode(' - ', $params['dateFrom']);
                if (count($explodeDate) == 1) {
                    // $startTime = Carbon::parse($explodeDate[0])->startOfDay();
                    // $endTime = Carbon::parse($explodeDate[0])->endOfDay();
                    return $query->where('created_at', Carbon::parse($explodeDate[0])->toDateString());
                }

                if (count($explodeDate) == 2) {
                    $startTime = Carbon::parse($explodeDate[0])->toDateString();
                    $endTime = Carbon::parse($explodeDate[1])->toDateString();
                    return $query->whereBetween('created_at', [$startTime, $endTime]);
                }
            });

        return $customers->paginate(isset($params['per_page']) ? $params['per_page'] : PerPage::PER_PAGE_10->value)
            ->withQueryString();
    }

    public function getCustomerByID(int $id): Customer
    {
        return $this->model->findOrFail($id);
    }

    public function destroy(int $id): void
    {
        $this->model->findOrFail($id)->delete();
    }
}
