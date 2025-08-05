<?php

namespace App\Providers;

use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Filament::serving(function () {
        //     FilamentView::registerRenderHook(
        //         'panels::body.start',
        //         fn () => view('filament.custom-styles')
        //     );
        // });
    }
}

