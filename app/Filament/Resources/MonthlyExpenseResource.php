<?php

namespace App\Filament\Resources;

use App\Models\MonthlyExpense;
use App\Models\Expense;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\MonthlyExpenseResource\Pages;

class MonthlyExpenseResource extends Resource
{
    protected static ?string $model = MonthlyExpense::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Mesečni troškovi';
    protected static ?string $navigationGroup = 'Inventar';


    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')
                ->label('Tip troška')
                ->options(self::getTypes())
                ->required(),
            Forms\Components\TextInput::make('description')
                ->label('Opis')
                ->maxLength(255),
            Forms\Components\TextInput::make('amount')
                ->label('Iznos (RSD)')
                ->numeric()
                ->required(),
            Forms\Components\Select::make('payment_type')
                ->label('Tip plaćanja')
                ->options(self::getPaymentTypes())
                ->required(),
            Forms\Components\TextInput::make('note')
                ->label('Napomena'),
            Forms\Components\TextInput::make('month')
                ->label('Mesec (YYYY-MM)')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')->label('Tip troška')->searchable(),
                Tables\Columns\TextColumn::make('description')->label('Opis')->searchable(),
                Tables\Columns\TextColumn::make('amount')->label('Iznos')->money('RSD', locale: 'sr_RS'),
                Tables\Columns\TextColumn::make('payment_type')->label('Tip plaćanja'),
                Tables\Columns\TextColumn::make('note')->label('Napomena')->wrap(),
                Tables\Columns\TextColumn::make('month')->label('Mesec')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMonthlyExpenses::route('/'),
            'create' => Pages\CreateMonthlyExpense::route('/create'),
            'edit'   => Pages\EditMonthlyExpense::route('/{record}/edit'),
        ];
    }

    /** Use values from ExpenseResource. */
    public static function getTypes(): array
    {
        return ExpenseResource::getTypes();
    }

    public static function getPaymentTypes(): array
    {
        return ExpenseResource::getPaymentTypes();
    }
}