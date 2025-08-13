<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BusinessDashboardExport implements WithMultipleSheets
{
    public function __construct(
        public Carbon $from,
        public Carbon $to,
        public array $data
    ) {}

    public function sheets(): array
    {
        return [
            new Sheets\SummarySheet($this->from, $this->to, $this->data),
            new Sheets\StatusesSheet($this->data),
            new Sheets\TypeShareSheet($this->data),
        ];
    }
}