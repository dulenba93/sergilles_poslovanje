<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BusinessDashboardExport;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class DashboardExportController extends Controller
{
    protected function range(Request $request): array
    {
        $from = Carbon::parse($request->query('from', Carbon::now()->subDays(30)->toDateString()))->startOfDay();
        $to   = Carbon::parse($request->query('to', Carbon::now()->toDateString()))->endOfDay();
        return [$from, $to];
    }

    protected function metrics($from, $to): array
    {
        $q = WorkOrder::query()->whereBetween('created_at', [$from, $to]);

        $totalOrders = (clone $q)->count();
        $expected    = (clone $q)->sum('total_price');
        $paid        = (clone $q)->sum('advance_payment');
        $remaining   = $expected - $paid;
        $montaze     = (clone $q)->sum('cena_montaze');

        $statusCounts = (clone $q)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->all();

        $typeShare = (clone $q)
            ->selectRaw('type, SUM(total_price) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->all();

        return compact('totalOrders','expected','paid','remaining','montaze','statusCounts','typeShare');
    }

    public function pdf(Request $request)
    {
        [$from, $to] = $this->range($request);
        $data = $this->metrics($from, $to);

        $pdf = Pdf::loadView('exports.dashboard-pdf', [
            'from' => $from,
            'to' => $to,
            'data' => $data,
        ]);

        return $pdf->download('Dashboard-'. $from->toDateString() .'_'. $to->toDateString() .'.pdf');
    }

    public function excel(Request $request)
    {
        [$from, $to] = $this->range($request);
        $data = $this->metrics($from, $to);

        return Excel::download(new BusinessDashboardExport($from, $to, $data),
            'Dashboard-'. $from->toDateString() .'_'. $to->toDateString() .'.xlsx');
    }
}