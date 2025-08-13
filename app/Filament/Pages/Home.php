<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Illuminate\Support\Carbon;
use App\Models\WorkOrder;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class Home extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Home';
    protected static ?string $title = 'Pregled Naloga';
    protected static ?string $slug = 'home'; // /admin/home
    protected static string $view = 'filament.pages.home';

    public ?string $from = null;
    public ?string $to = null;

    public function mount(): void
    {
        // default: poslednjih 30 dana
        $this->from = request()->query('from') ?: Carbon::now()->subDays(30)->startOfDay()->toDateString();
        $this->to   = request()->query('to')   ?: Carbon::now()->endOfDay()->toDateString();
    }

    protected function getForms(): array
    {
        return [
            'filtersForm' => $this->makeForm()
                ->schema([
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
                                    $this->from = Carbon::now()->subDays(30)->startOfDay()->toDateString();
                                    $this->to   = Carbon::now()->endOfDay()->toDateString();
                                }),
                        ])->columnSpan(6)->alignRight(),
                    ]),
                ])
                ->statePath('data'),
        ];
    }

    public function submitFiltersForm(): void
    {
        $state = $this->filtersForm->getState();
        $this->from = data_get($state, 'from', $this->from);
        $this->to   = data_get($state, 'to', $this->to);
    }

    public function getRange(): array
    {
        $from = Carbon::parse($this->from)->startOfDay();
        $to   = Carbon::parse($this->to)->endOfDay();
        return [$from, $to];
    }

    public function getMetrics(): array
    {
        [$from, $to] = $this->getRange();

        $q = WorkOrder::query()->whereBetween('created_at', [$from, $to]);

        $totalOrders   = (clone $q)->count();
        $expected      = (clone $q)->sum('total_price');
        $paid          = (clone $q)->sum('advance_payment');
        $remaining     = $expected - $paid;
        $montaze       = (clone $q)->sum('cena_montaze');

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

        return [
            'totalOrders'  => (int) $totalOrders,
            'expected'     => (float) $expected,
            'paid'         => (float) $paid,
            'remaining'    => (float) $remaining,
            'montaze'      => (float) $montaze,
            'statusCounts' => $statusCounts,
            'typeShare'    => $typeShare,
        ];
    }
}