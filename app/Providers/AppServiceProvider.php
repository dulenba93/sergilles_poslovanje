<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;


class AppServiceProvider extends ServiceProvider
{

    
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        \Livewire\Livewire::component('filament.widgets.status-count-chart', \App\Filament\Widgets\StatusCountChart::class);
        \Livewire\Livewire::component('filament.widgets.revenue-by-type-pie', \App\Filament\Widgets\RevenueByTypePie::class);
    }
}
