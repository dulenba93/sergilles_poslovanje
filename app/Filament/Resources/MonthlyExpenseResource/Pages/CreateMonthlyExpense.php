<?php

namespace App\Filament\Resources\MonthlyExpenseResource\Pages;

use App\Filament\Resources\MonthlyExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMonthlyExpense extends CreateRecord
{
    protected static string $resource = MonthlyExpenseResource::class;
}
