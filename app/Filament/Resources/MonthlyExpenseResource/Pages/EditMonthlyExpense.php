<?php

namespace App\Filament\Resources\MonthlyExpenseResource\Pages;

use App\Filament\Resources\MonthlyExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMonthlyExpense extends EditRecord
{
    protected static string $resource = MonthlyExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
