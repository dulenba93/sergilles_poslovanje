<?php

namespace App\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class StatusCountChart extends ApexChartWidget
{
    protected static ?string $heading = 'Statusi radnih naloga';

    // Prosledite preko Livewire parametara
    public ?array $labels = [];
    public ?array $values = [];

    protected function getOptions(): array
    {
        return [
            'chart' => [
                'type' => 'bar',
                'height' => 320,
            ],
            'series' => [
                [
                    'name' => 'Broj naloga',
                    'data' => $this->values ?: [],
                ],
            ],
            'xaxis' => [
                'categories' => $this->labels ?: [],
            ],
        ];
    }
}