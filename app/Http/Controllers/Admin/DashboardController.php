<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RevenueReportExport;
use App\Http\Controllers\BaseController;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DashboardController extends BaseController
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    // Hiển thị dashboard thống kê
    public function index(Request $request)
    {
        $report = $this->dashboardService->getDashboardReport($request->all());

        return view('dashboard.index', $report);
    }

    // Xuất báo cáo doanh thu Excel theo bộ lọc
    public function exportRevenue(Request $request): BinaryFileResponse
    {
        $rows = $this->dashboardService->getRevenueRows($request->all());
        $fileName = 'revenue-report-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new RevenueReportExport($rows), $fileName);
    }
}
