<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StaffEditRequest;
use App\Http\Requests\StaffRequest;
use App\Services\StaffService;
use Illuminate\Http\Request;

class StaffController extends BaseController
{
    public function __construct(
        private StaffService $staffService
    ) {}

    public function index(Request $request)
    {
        $staffs = $this->staffService->getStaffs($request->all());

        return view('staff.index', compact('staffs'));
    }

    public function store(StaffRequest $request)
    {
        $this->staffService->createStaff($request->validated());

        return redirect()->back()->with('dataSuccess', 'Tạo mới nhân viên thành công');
    }

    public function edit(int $id)
    {
        return $this->staffService->getStaffByID($id);
    }

    public function update(int $id, StaffEditRequest $request)
    {
        $this->staffService->updateStaffByID($id, $request->validated());

        return redirect()->back()->with('dataSuccess', 'Cập nhật nhân viên thành công');
    }

    public function destroy(int $id)
    {
        $this->staffService->destroy($id);

        return redirect()->back()->with('dataSuccess', 'Vô hiệu hóa nhân viên thành công');
    }

    public function restore(int $id)
    {
        $this->staffService->restore($id);

        return redirect()->back()->with('dataSuccess', 'Khôi phục nhân viên thành công');
    }

    public function forceDelete(int $id)
    {
        $this->staffService->forceDelete($id);

        return redirect()->back()->with('dataSuccess', 'Xóa vĩnh viễn nhân viên thành công');
    }
}
