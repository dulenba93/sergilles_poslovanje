<?php

namespace App\Filament\Resources\MonthlyExpenseResource\Pages;

use App\Filament\Resources\MonthlyExpenseResource;
use App\Models\MonthlyExpense;
use App\Models\Expense;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Forms;
class ListMonthlyExpenses extends ListRecords
{
    protected static string $resource = MonthlyExpenseResource::class;
protected function getHeaderActions(): array
{
    return [
        // +Create dugme
        Actions\CreateAction::make(),
        // Dugme za prenos mesečnih troškova u glavnu tabelu
        Actions\Action::make('submit')
            ->label('Unesi mesečne troškove')
            ->icon('heroicon-m-plus')
            // mini-forma za unos meseca
            ->form([
                Forms\Components\TextInput::make('month')
                    ->label('Troskovi za mesec (YYYY-MM)')
                    ->placeholder('npr. ' . now()->format('Y-m'))
                    ->required(),
            ])
            ->requiresConfirmation()
            ->action(function (array $data) {
                $month = $data['month'] ?? now()->format('Y-m');
                $monthlyExpenses = MonthlyExpense::all();
                foreach ($monthlyExpenses as $item) {
                    Expense::create([
                        'type'         => $item->type,
                        'description'  => $item->description,
                        'amount'       => $item->amount,
                        'payment_type' => $item->payment_type,
                        'note'         => $item->note,
                        'month'        => $month,
                    ]);
                }
                // Ne brišemo privremene stavke; ostaju u listi
                Notification::make()
                    ->title('Svi mesečni troškovi su uspešno uneti u evidenciju troškova za mesec ' . $month . '.')
                    ->success()
                    ->send();
            }),
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
        Notification::make()
            ->title('Svi mesečni troškovi su uspešno uneti u evidenciju troškova.')
            ->success()
            ->send();
    }
}
