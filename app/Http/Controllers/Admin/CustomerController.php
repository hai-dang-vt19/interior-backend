<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CustomerEditRequest;
use App\Http\Requests\CustomerRequest;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends BaseController
{
    public function __construct(
        private CustomerService $customerService
    ) {}

    public function index(Request $request)
    {
        $customers = $this->customerService->getCustomers($request->all());
        return view('customer.index', compact('customers'));
    }

    public function edit(int $id)
    {
        return $this->customerService->getCustomerByID($id);
    }

    public function update(int $id, CustomerEditRequest $request)
    {
        $params = $request->validated();
        $this->customerService->updateCustomerByID($id, $params);

        return redirect()->back()->with('dataSuccess', 'Cập nhật khách hàng thành công');
    }

    public function destroy(int $id)
    {
        $this->customerService->destroy($id);
        return redirect()->back()->with('dataSuccess', 'Xóa khách hàng thành công');
    }
}
