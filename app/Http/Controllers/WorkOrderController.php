<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProformaExport;
use Illuminate\Http\Request;

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

    public function exportWeekPdf(\Illuminate\Http\Request $request)
{
    // week_start može doći iz linka; ako nije, koristi "danas"
    $weekStart = \Illuminate\Support\Carbon::parse(
        $request->get('week_start', now()->toDateString())
    )->startOfWeek(\Illuminate\Support\Carbon::MONDAY)->startOfDay();

    $weekEnd = (clone $weekStart)->endOfWeek(\Illuminate\Support\Carbon::SUNDAY)->endOfDay();

    // Učitaj samo zakazane naloge za datu nedelju
    $orders = \App\Models\WorkOrder::query()
        ->select([
            'id', 'code', 'customer_name', 'phone', 'email', 'address',
            'status', 'scheduled_at', 'total_price', 'advance_payment', 'type',
        ])
        ->whereNotNull('scheduled_at')
        ->whereBetween('scheduled_at', [$weekStart, $weekEnd])
        ->orderBy('scheduled_at')
        ->get();

    // Grupisanje po datumu (Y-m-d)
    $grouped = $orders->groupBy(function ($o) {
        return \Illuminate\Support\Carbon::parse($o->scheduled_at)->toDateString();
    });

    // Za header i “prazne” dane
    $days = collect(range(0, 6))->map(fn ($i) => (clone $weekStart)->addDays($i));

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('work-orders.week-pdf', [
        'weekStart' => $weekStart,
        'weekEnd'   => $weekEnd,
        'days'      => $days,
        'grouped'   => $grouped,
    ])->setPaper('a4', 'portrait');

    $fileName = 'Montaže-' . $weekStart->format('Ymd') . '-' . $weekEnd->format('Ymd') . '.pdf';
    return $pdf->download($fileName);
}


public function exportProformaPdf(Request $request, \App\Models\WorkOrder $workOrder)
{
    // Učitaj relacije potrebne za izračun stavki
    $workOrder->load([
        'positions',
        'positions.metraza.product.vendor',
        'positions.garnisna.product.vendor',
        'positions.roloZebra.product.vendor',
        'positions.plise.product.vendor',
    ]);

    // ---- Opcije iz dijaloga ----
    $racunKey = $request->get('racun', 'firma');            // 'firma' | 'dusan'
    $pdvIncluded = (bool) $request->boolean('pdv_included'); // 1 => uračunat

    // Brojevi računa:
    $racuni = [
        'firma' => '265-6240310000065-53',
        'dusan' => '115-0000000066773-50',
    ];
    $accountNumber = $racuni[$racunKey] ?? $racuni['firma'];

    // Grupisanje kao i za Excel (po nazivu pozicije)
    $grouped = $workOrder->positions->groupBy(fn ($p) => $p->naziv ?: 'Bez naziva');

    // Prosledi u view i opciju PDV-a + izabrani račun
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('work-orders.proforma-pdf', [
        'workOrder'      => $workOrder,
        'grouped'        => $grouped,
        'pdvIncluded'    => $pdvIncluded,   // NOVO
        'accountNumber'  => $accountNumber, // NOVO
    ])->setPaper('a4', 'portrait');

    return $pdf->download("Profaktura-{$workOrder->code}.pdf");
}


}
