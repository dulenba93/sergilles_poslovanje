<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;

class WorkOrderController extends Controller
{
    /**
     * Exportuje radni nalog u PDF sa pozicijama grupisanim po 'naziv'.
     */
    public function exportPdf(WorkOrder $workOrder)
    {
        // Load pivot + sve potencijalne modele pozicija
        $workOrder->load([
            'positions.metraza',
            'positions.garnisna',
            'positions.roloZebra',
            'positions.plise',
        ]);

        // Grupisanje po nazivu (iz work_order_positions.naziv)
        $grouped = $workOrder->positions->groupBy(fn ($p) => $p->naziv ?: 'Bez naziva');

        // Render PDF view-a
        $pdf = Pdf::loadView('work-orders.pdf', [
            'workOrder'        => $workOrder,
            'groupedPositions' => $grouped,
        ]);

        return $pdf->download("SerGilles-nalog-{$workOrder->code}.pdf");
    }
}
