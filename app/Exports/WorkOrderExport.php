<?php

namespace App\Exports;

use App\Models\WorkOrder;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class WorkOrderExport implements FromView
{
    public function __construct(protected WorkOrder $workOrder) {}

    public function view(): View
    {
        $this->workOrder->load([
            'positions.metraza',
            'positions.garnisna',
            'positions.roloZebra',
            'positions.plise',
        ]);

        $grouped = $this->workOrder->positions->groupBy(fn ($p) => $p->naziv ?: 'Bez naziva');

        return view('exports.work_order_excel', [
            'record'  => $this->workOrder,
            'grouped' => $grouped,
        ]);
    }
}
