<?php
namespace App\Filament\Resources;

use App\Models\Expense;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';
    protected static ?string $navigationLabel = 'Troškovi';
    protected static ?string $navigationGroup = 'IZLAZ';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                    ->placeholder(now()->format('Y-m'))    // automatski placeholder
                    ->default(now()->format('Y-m'))        // automatski default value
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tip troška')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Opis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Iznos')
                    ->money('RSD', locale: 'sr_RS'),
                Tables\Columns\TextColumn::make('payment_type')
                    ->label('Tip plaćanja')
                    ->sortable(),
                Tables\Columns\TextColumn::make('note')
                    ->label('Napomena')
                    ->wrap(),
                Tables\Columns\TextColumn::make('month')
                    ->label('Mesec')
                    ->sortable(),
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
            'index'  => ExpenseResource\Pages\ListExpenses::route('/'),
            'create' => ExpenseResource\Pages\CreateExpense::route('/create'),
            'edit'   => ExpenseResource\Pages\EditExpense::route('/{record}/edit'),
        ];
    }

    /** Liste sa srpskim vrednostima za tip troška i tip plaćanja. */
    public static function getTypes(): array
    {
        return [
            'Šivenje'     => 'Šivenje',
            'Reklama'     => 'Reklama',
            'Gorivo'      => 'Gorivo',
            'Nabavka'     => 'Nabavka',
            'Plate'       => 'Plate',
            'Greške'      => 'Greške',
            'Porezi'      => 'Porezi',
            'Op Troškovi' => 'Op Troškovi',
        ];
    }

    public static function getPaymentTypes(): array
    {
        return [
            'KES'   => 'KES',
            'FIRMA' => 'FIRMA',
        ];
    }
}