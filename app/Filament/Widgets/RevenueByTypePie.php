<?php

namespace App\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class RevenueByTypePie extends ApexChartWidget
{
    protected static ?string $heading = 'Prihod po tipu naloga';

    public ?array $labels = [];
    public ?array $values = [];

    protected function getOptions(): array
    {
        return [
            'chart' => [
                'type' => 'pie',
                'height' => 320,
            ],
            'labels' => $this->labels ?: [],
            'series' => $this->values ?: [],
            'legend' => [
                'position' => 'bottom',
            ],
        ];
    }
}