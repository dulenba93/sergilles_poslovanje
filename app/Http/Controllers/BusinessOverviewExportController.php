<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BusinessOverviewExport;

// MODELI
use App\Models\WorkOrder;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\VendorPayment;

class BusinessOverviewExportController extends Controller
{
    protected function compute(string $from, string $to): array
    {
        $fromC = Carbon::parse($from)->startOfDay();
        $toC   = Carbon::parse($to)->endOfDay();

        // Work orders (advance_payment; tip_placanja)
        $wo = WorkOrder::query()->whereBetween('created_at', [$fromC, $toC]);
        $wo_paid_total = (clone $wo)->sum('advance_payment');
        $wo_paid_kes   = (clone $wo)->where('tip_placanja', 'KES')->sum('advance_payment');
        $wo_paid_firma = (clone $wo)->where('tip_placanja', 'FIRMA')->sum('advance_payment');

        // Sales (paid_amount; payment_type)
        $sa = Sale::query()->whereBetween('created_at', [$fromC, $toC]);
        $sale_paid_total = (clone $sa)->sum('paid_amount');
        $sale_paid_kes   = (clone $sa)->where('payment_type', 'KES')->sum('paid_amount');
        $sale_paid_firma = (clone $sa)->where('payment_type', 'FIRMA')->sum('paid_amount');

        // Inflows
        $in_total = $wo_paid_total + $sale_paid_total;
        $in_kes   = $wo_paid_kes   + $sale_paid_kes;
        $in_firma = $wo_paid_firma + $sale_paid_firma;

        // Expenses (expenses.created_at)
        $ex = Expense::query()->whereBetween('created_at', [$fromC, $toC]);
        $exp_total = (clone $ex)->sum('amount');
        $exp_kes   = (clone $ex)->where('payment_type', 'KES')->sum('amount');
        $exp_firma = (clone $ex)->where('payment_type', 'FIRMA')->sum('amount');

        // Vendor payments (payment_date)
        $vp = VendorPayment::query()->whereBetween('payment_date', [$fromC->toDateString(), $toC->toDateString()]);
        $vp_total = (clone $vp)->sum('amount');
        $vp_kes   = (clone $vp)->where('payment_type', 'KES')->sum('amount');
        $vp_firma = (clone $vp)->where('payment_type', 'FIRMA')->sum('amount');

        // Outflows
        $out_total = $exp_total + $vp_total;
        $out_kes   = $exp_kes   + $vp_kes;
        $out_firma = $exp_firma + $vp_firma;

        // NET
        $net_total = $in_total - $out_total;
        $net_kes   = $in_kes   - $out_kes;
        $net_firma = $in_firma - $out_firma;

        return [
            'from' => $fromC, 'to' => $toC,
            'wo_paid_total' => (float) $wo_paid_total,
            'wo_paid_kes'   => (float) $wo_paid_kes,
            'wo_paid_firma' => (float) $wo_paid_firma,
            'sale_paid_total' => (float) $sale_paid_total,
            'sale_paid_kes'   => (float) $sale_paid_kes,
            'sale_paid_firma' => (float) $sale_paid_firma,
            'in_total' => (float) $in_total,
            'in_kes'   => (float) $in_kes,
            'in_firma' => (float) $in_firma,
            'exp_total' => (float) $exp_total,
            'exp_kes'   => (float) $exp_kes,
            'exp_firma' => (float) $exp_firma,
            'vp_total' => (float) $vp_total,
            'vp_kes'   => (float) $vp_kes,
            'vp_firma' => (float) $vp_firma,
            'out_total' => (float) $out_total,
            'out_kes'   => (float) $out_kes,
            'out_firma' => (float) $out_firma,
            'net_total' => (float) $net_total,
            'net_kes'   => (float) $net_kes,
            'net_firma' => (float) $net_firma,
        ];
    }

    public function pdf(Request $request)
    {
        $from = $request->query('from') ?? now()->startOfMonth()->toDateString();
        $to   = $request->query('to')   ?? now()->toDateString();

        $data = $this->compute($from, $to);

        $pdf = Pdf::loadView('exports.business-overview', $data);

        return $pdf->download("Pregled-poslovanja_{$data['from']->format('Ymd')}-{$data['to']->format('Ymd')}.pdf");
    }

    public function excel(Request $request)
    {
        $from = $request->query('from') ?? now()->startOfMonth()->toDateString();
        $to   = $request->query('to')   ?? now()->toDateString();

        $data = $this->compute($from, $to);

        return Excel::download(new BusinessOverviewExport($data), "Pregled-poslovanja_{$data['from']->format('Ymd')}-{$data['to']->format('Ymd')}.xlsx");
    }
}