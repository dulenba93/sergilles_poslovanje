<?php

namespace App\Filament\Resources\MonthlyExpenseResource\Pages;

use App\Filament\Resources\MonthlyExpenseResource;
use App\Models\MonthlyExpense;
use App\Models\Expense;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Notifications\Notification;

class ListMonthlyExpenses extends ListRecords
{
    protected static string $resource = MonthlyExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('submit')
                ->label('Unesi mesečne troškove')
                ->icon('heroicon-m-plus')
                ->requiresConfirmation()
                ->action('submit'), // poziva javnu metodu submit() ispod
        ];
    }

    public function submit()
    {
        $monthlyExpenses = MonthlyExpense::all();
        foreach ($monthlyExpenses as $item) {
            Expense::create([
                'type'         => $item->type,
                'description'  => $item->description,
                'amount'       => $item->amount,
                'payment_type' => $item->payment_type,
                'note'         => $item->note,
                'month'        => $item->month,
            ]);
        }
        MonthlyExpense::truncate();

        Notification::make()
            ->title('Svi mesečni troškovi su uspešno uneti u evidenciju troškova.')
            ->success()
            ->send();
    }
}
