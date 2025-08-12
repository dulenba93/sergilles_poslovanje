<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * Export class for generating pro-forma invoices (profaktura) in Excel format.
 *
 * This export receives a work order and a grouped collection of positions and
 * passes them to a dedicated Blade view which renders the tabular invoice.
 */
class ProformaExport implements FromView
{
    /**
     * The work order instance.
     *
     * @var mixed
     */
    protected $workOrder;

    /**
     * Collection of positions grouped by name.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $groupedPositions;

    /**
     * Instantiate the export.
     *
     * @param  mixed  $workOrder
     * @param  \Illuminate\Support\Collection  $groupedPositions
     * @return void
     */
    public function __construct($workOrder, $groupedPositions)
    {
        $this->workOrder = $workOrder;
        $this->groupedPositions = $groupedPositions;
    }

    /**
     * Return a view containing data for the Excel export.
     *
     * @return View
     */
    public function view(): View
    {
        return view('exports.proforma_excel', [
            'workOrder' => $this->workOrder,
            'grouped'   => $this->groupedPositions,
        ]);
    }
}
