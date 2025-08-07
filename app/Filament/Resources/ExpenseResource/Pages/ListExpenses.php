<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

   protected function getHeaderActions(): array
{
    return [
        // Standardno dugme za novi trošak
        Actions\CreateAction::make(),
        // Dugme za pristup mesečnim troškovima
        Actions\Action::make('monthly')
            ->label('Mesečni troškovi')
            ->icon('heroicon-m-calendar-days')
            ->url(fn () => \App\Filament\Resources\MonthlyExpenseResource::getUrl('index'))
            ->color('primary'),
    ];
}
}
