<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CustomerAddressRequest;
use App\Http\Requests\CustomerContactRequest;
use App\Http\Requests\CustomerEditRequest;
use App\Http\Requests\CustomerRequest;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    // Tạo mới khách hàng từ màn hình quản trị
    public function store(CustomerRequest $request)
    {
        $params = $request->validated();
        $this->customerService->createCustomer($params);

        return redirect()->back()->with('dataSuccess', 'Tạo mới khách hàng thành công');
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

    public function restore(int $id)
    {
        $this->customerService->restore($id);

        return redirect()->back()->with('dataSuccess', 'Khôi phục khách hàng thành công');
    }

    public function forceDelete(int $id)
    {
        $this->customerService->forceDelete($id);

        return redirect()->back()->with('dataSuccess', 'Xóa vĩnh viễn khách hàng thành công');
    }

    // Màn hình quản lý địa chỉ và liên hệ khách hàng
    public function profile(int $id)
    {
        $customer = $this->customerService->getCustomerProfile($id);

        return view('customer.profile', compact('customer'));
    }

    public function addAddress(int $id, CustomerAddressRequest $request)
    {
        $this->customerService->addAddress($id, $request->validated());

        return redirect()->back()->with('dataSuccess', 'Thêm địa chỉ thành công');
    }

    public function deleteAddress(int $id, int $addressId)
    {
        $this->customerService->deleteAddress($id, $addressId);

        return redirect()->back()->with('dataSuccess', 'Xóa địa chỉ thành công');
    }

    public function addContact(int $id, CustomerContactRequest $request)
    {
        $params = $request->validated();
        $params['contacted_by'] = Auth::id();
        $this->customerService->addContactLog($id, $params);

        return redirect()->back()->with('dataSuccess', 'Đã ghi nhận liên hệ khách hàng');
    }
}
