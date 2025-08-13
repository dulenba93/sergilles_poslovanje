<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalesExport implements WithMultipleSheets
{
    public function __construct(public Collection $sales) {}

    public function sheets(): array
    {
        // Sheet 1: Sažetak (po grupama = product.name)
        $groups = $this->sales->groupBy(fn ($s) => $s->product?->name ?: $s->code);

        $sheets = [
            new Sheets\SalesFlatSheet($this->sales),
        ];

        foreach ($groups as $groupName => $items) {
            $sheets[] = new Sheets\SalesGroupSheet($groupName, $items);
        }

        return $sheets;
    }
}