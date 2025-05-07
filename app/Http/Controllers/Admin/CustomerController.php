<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
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

    public function destroy(Request $request, int $id)
    {
        $this->customerService->destroy($id);
        return redirect()->back()->with('dataSuccess', 'Xóa khách hàng thành công');
    }
}
