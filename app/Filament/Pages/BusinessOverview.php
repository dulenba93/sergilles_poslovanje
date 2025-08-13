<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\WorkOrder;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\VendorPayment;

class BusinessOverview extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Pregled poslovanja';
    protected static ?string $title           = 'Pregled poslovanja';
    protected static ?string $slug            = 'business-overview'; // /admin/business-overview
    protected static ?int    $navigationSort  = 11; // odmah ispod Home
    protected static string  $view            = 'filament.pages.business-overview';

    public ?string $from = null;
    public ?string $to   = null;

    public function mount(): void
    {
        // Default: od početka TEKUĆEG meseca do danas
        $this->from = request()->query('from') ?: Carbon::now()->startOfMonth()->toDateString();
        $this->to   = request()->query('to')   ?: Carbon::now()->endOfDay()->toDateString();
    }

    protected function getForms(): array
    {
        return [
            'filtersForm' => $this->makeForm()->schema([
                Forms\Components\Grid::make(12)->schema([
                    Forms\Components\DatePicker::make('from')
                        ->label('Od datuma')
                        ->default(fn () => $this->from)
                        ->displayFormat('d.m.Y')
                        ->native(false)
                        ->columnSpan(3),

                    Forms\Components\DatePicker::make('to')
                        ->label('Do datuma')
                        ->default(fn () => $this->to)
                        ->displayFormat('d.m.Y')
                        ->native(false)
                        ->columnSpan(3),

                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('apply')
                            ->label('Primeni')
                            ->submit('filtersForm'),
                        Forms\Components\Actions\Action::make('reset')
                            ->label('Reset')
                            ->action(function () {
                                $this->from = Carbon::now()->startOfMonth()->toDateString();
                                $this->to   = Carbon::now()->endOfDay()->toDateString();
                            }),
                    ])->columnSpan(6)->alignRight(),
                ]),
            ])->statePath('data'),
        ];
    }

    public function submitFiltersForm(): void
    {
        $state     = $this->filtersForm->getState();
        $this->from = data_get($state, 'from', $this->from);
        $this->to   = data_get($state, 'to',   $this->to);
    }

    public function getRange(): array
    {
        $from = Carbon::parse($this->from)->startOfDay();
        $to   = Carbon::parse($this->to)->endOfDay();
        return [$from, $to];
    }

    public function getOverview(): array
    {
        [$from, $to] = $this->getRange();

        // === UPLATE (PRILIVI) ===
        // Work orders: sum(advance_payment), po tipu plaćanja (tip_placanja: 'KES'/'FIRMA')
        $woBase = WorkOrder::query()->whereBetween('created_at', [$from, $to]);

        $wo_paid_total  = (clone $woBase)->sum('advance_payment');
        $wo_paid_kes    = (clone $woBase)->where('tip_placanja', 'KES')->sum('advance_payment');
        $wo_paid_firma  = (clone $woBase)->where('tip_placanja', 'FIRMA')->sum('advance_payment');

        // Sales: sum(paid_amount), po payment_type: 'KES'/'FIRMA'
        $saleBase = Sale::query()->whereBetween('created_at', [$from, $to]);

        $sale_paid_total = (clone $saleBase)->sum('paid_amount');
        $sale_paid_kes   = (clone $saleBase)->where('payment_type', 'KES')->sum('paid_amount');
        $sale_paid_firma = (clone $saleBase)->where('payment_type', 'FIRMA')->sum('paid_amount');

        // Zajedno (uplate)
        $in_total = $wo_paid_total + $sale_paid_total;
        $in_kes   = $wo_paid_kes   + $sale_paid_kes;
        $in_firma = $wo_paid_firma + $sale_paid_firma;

        // === RASHODI (MINUS) ===
        // Expenses: amount (payment_type KES/FIRMA), koristimo created_at u opsegu
        $expBase = Expense::query()->whereBetween('created_at', [$from, $to]);

        $exp_total = (clone $expBase)->sum('amount');
        $exp_kes   = (clone $expBase)->where('payment_type', 'KES')->sum('amount');
        $exp_firma = (clone $expBase)->where('payment_type', 'FIRMA')->sum('amount');

        // Vendor payments: amount, payment_type, opseg po payment_date
        $vpBase = VendorPayment::query()->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()]);

        $vp_total = (clone $vpBase)->sum('amount');
        $vp_kes   = (clone $vpBase)->where('payment_type', 'KES')->sum('amount');
        $vp_firma = (clone $vpBase)->where('payment_type', 'FIRMA')->sum('amount');

        // Rashodi kao negativni iznosi (za prikaz)
        $out_total = $exp_total + $vp_total;
        $out_kes   = $exp_kes   + $vp_kes;
        $out_firma = $exp_firma + $vp_firma;

        // === FINALNI PRESEK ===
        $net_total = $in_total - $out_total;
        $net_kes   = $in_kes   - $out_kes;
        $net_firma = $in_firma - $out_firma;

        return [
            'range' => ['from' => $from, 'to' => $to],

            // Uplate iz radnih naloga
            'wo_paid_total' => (float) $wo_paid_total,
            'wo_paid_kes'   => (float) $wo_paid_kes,
            'wo_paid_firma' => (float) $wo_paid_firma,

            // Uplate iz prodaja
            'sale_paid_total' => (float) $sale_paid_total,
            'sale_paid_kes'   => (float) $sale_paid_kes,
            'sale_paid_firma' => (float) $sale_paid_firma,

            // Zajedno uplate
            'in_total' => (float) $in_total,
            'in_kes'   => (float) $in_kes,
            'in_firma' => (float) $in_firma,

            // Troškovi (rashodi) – kao pozitivne sume, ali ćemo ih prikazati sa minusom
            'exp_total' => (float) $exp_total,
            'exp_kes'   => (float) $exp_kes,
            'exp_firma' => (float) $exp_firma,

            // Plaćanja dobavljačima (rashodi)
            'vp_total' => (float) $vp_total,
            'vp_kes'   => (float) $vp_kes,
            'vp_firma' => (float) $vp_firma,

            // Zajedno rashodi
            'out_total' => (float) $out_total,
            'out_kes'   => (float) $out_kes,
            'out_firma' => (float) $out_firma,

            // Finalni presek
            'net_total' => (float) $net_total,
            'net_kes'   => (float) $net_kes,
            'net_firma' => (float) $net_firma,
        ];
    }
}