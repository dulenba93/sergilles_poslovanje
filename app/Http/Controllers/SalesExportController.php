<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SalesExportController extends Controller
{
    protected function getSelected(Request $request): Collection
    {
        $ids = collect(explode(',', (string) $request->query('ids')))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        return Sale::with('product')->whereIn('id', $ids)->get();
    }

    public function pdf(Request $request)
    {
        $sales = $this->getSelected($request);

        // Grupisanje po nazivu (ako postoji product.name, inače po code)
        $grouped = $sales->groupBy(function ($sale) {
            return $sale->product?->name ?: $sale->code;
        });

        $pdf = Pdf::loadView('exports.sales-selected', [
            'grouped' => $grouped,
        ]);

        return $pdf->download('Prodaje-selektovano.pdf');
    }

    public function excel(Request $request)
    {
        $sales = $this->getSelected($request);

        return Excel::download(new SalesExport($sales), 'Prodaje-selektovano.xlsx');
    }
}