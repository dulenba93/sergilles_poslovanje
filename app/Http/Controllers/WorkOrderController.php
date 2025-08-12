<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProformaExport;

/**
 * Controller responsible for handling operations related to work orders.
 */
class WorkOrderController extends Controller
{
    /**
     * Export a work order into a PDF document grouped by 'naziv'.
     *
     * This method loads the necessary position relations, groups positions
     * by their display name and renders a dedicated Blade view into a PDF.
     *
     * @param  WorkOrder  $workOrder
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportPdf(WorkOrder $workOrder)
    {
        // Eager-load all possible position relations on the work order.
        $workOrder->load([
            'positions.metraza',
            'positions.garnisna',
            'positions.roloZebra',
            'positions.plise',
        ]);

        // Group positions by their 'naziv' attribute (fallback to 'Bez naziva').
        $grouped = $workOrder->positions->groupBy(fn ($p) => $p->naziv ?: 'Bez naziva');

        // Render the PDF using the existing view.
        $pdf = Pdf::loadView('work-orders.pdf', [
            'workOrder'        => $workOrder,
            'groupedPositions' => $grouped,
        ]);

        // Download the generated PDF. The filename includes the work order code.
        return $pdf->download("SerGilles-nalog-{$workOrder->code}.pdf");
    }

    /**
     * Export a pro-forma invoice (profaktura) for the given work order as an Excel file.
     *
     * A profaktura lists each position with its length/quantity, price and VAT.
     * At the end, a montage service is included if its price is greater than zero.
     *
     * @param  WorkOrder  $workOrder
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportProforma(WorkOrder $workOrder)
    {
        // Eager-load all possible position relations on the work order.
        $workOrder->load([
            'positions.metraza',
            'positions.garnisna',
            'positions.roloZebra',
            'positions.plise',
        ]);

        // Group positions by their 'naziv' attribute.
        $grouped = $workOrder->positions->groupBy(fn ($p) => $p->naziv ?: 'Bez naziva');

        // Generate and return the Excel file using a dedicated export class.
        return Excel::download(new ProformaExport($workOrder, $grouped), "Profaktura-{$workOrder->code}.xlsx");
    }
}
