<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Illuminate\Routing\Controller; // koristimo BaseController direktno
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WorkOrderExport;
use Barryvdh\DomPDF\Facade\Pdf;

class WorkOrderExportController extends Controller
{
    public function pdf(WorkOrder $workOrder)
    {
        // Eager-load svih mogućih relacija sa pivotom positions
        $workOrder->load([
            'positions.metraza',
            'positions.garnisna',
            'positions.roloZebra',
            'positions.plise',
        ]);

        // Grupisanje po pivot->naziv (ako je prazno, "Bez naziva")
        $grouped = $workOrder->positions->groupBy(fn ($p) => $p->naziv ?: 'Bez naziva');

        $pdf = Pdf::loadView('exports.work_order_pdf', [
            'record'  => $workOrder,
            'grouped' => $grouped,
        ])->setPaper('a4');

        $fileName = 'Radni_nalog_' . ($workOrder->code ?? $workOrder->id) . '.pdf';
        return $pdf->download($fileName);
    }

    public function excel(WorkOrder $workOrder)
    {
        // Excel export iz view-a
        return Excel::download(
            new WorkOrderExport($workOrder),
            'Radni_nalog_' . ($workOrder->code ?? $workOrder->id) . '.xlsx'
        );
    }
}
