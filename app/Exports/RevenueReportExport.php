<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class RevenueReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    public function __construct(
        private array $rows
    ) {}

    public function headings(): array
    {
        return ['Date', 'Orders', 'Revenue'];
    }

    public function array(): array
    {
        return array_map(function (array $row) {
            return [
                $row['date'],
                $row['orders_count'],
                $row['revenue'],
            ];
        }, $this->rows);
    }

    public function title(): string
    {
        return 'Revenue Report';
    }
}
