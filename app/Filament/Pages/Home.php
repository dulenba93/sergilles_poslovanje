<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Home extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.home';

    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationGroup = null;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}

