<?php

namespace App\Filament\Resources;

use App\Models\VendorPayment;
use App\Models\Vendor;
use Filament\Forms;
    use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\VendorPaymentResource\Pages;

class VendorPaymentResource extends Resource
{
    protected static ?string $model = VendorPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Plaćanja dobavljačima';
    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('vendor_id')
                    ->label('Dobavljač')
                    ->options(fn () => Vendor::pluck('name', 'id'))
                    ->searchable()
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
                    ->options([
                        'KES'   => 'Keš',
                        'FIRMA' => 'Firma',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('note')
                    ->label('Napomena'),
                Forms\Components\DatePicker::make('payment_date')
                    ->label('Datum plaćanja')
                    ->required()
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Dobavljač')
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
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Datum plaćanja')
                    ->date('Y-m-d')
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
            'index'  => Pages\ListVendorPayments::route('/'),
            'create' => Pages\CreateVendorPayment::route('/create'),
            'edit'   => Pages\EditVendorPayment::route('/{record}/edit'),
        ];
    }
}