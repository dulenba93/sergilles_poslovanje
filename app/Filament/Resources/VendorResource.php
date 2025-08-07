<?php

namespace App\Filament\Resources;

use App\Models\Vendor;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\VendorResource\Pages;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Dobavljači';
        protected static ?string $navigationGroup = 'Inventar';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Dobavljač')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('vendor_type')
                    ->label('Tip dobavljača')
                    ->options([
                        'Domaci' => 'Domaći',
                        'Strani' => 'Strani',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('contact')
                    ->label('Kontakt osoba')
                    ->maxLength(255),
                Forms\Components\TextInput::make('website')
                    ->label('Web sajt')
                    ->maxLength(255),
                Forms\Components\TextInput::make('contact_info')
                    ->label('Kontakt (e‑mail/telefon)')
                    ->maxLength(255),
                Forms\Components\Textarea::make('notes')
                    ->label('Napomene')
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Dobavljač')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vendor_type')
                    ->label('Tip')
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact')
                    ->label('Kontakt osoba'),
                Tables\Columns\TextColumn::make('contact_info')
                    ->label('Kontakt'),
                Tables\Columns\TextColumn::make('website')
                    ->label('Web sajt'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Napomene')
                    ->wrap(),
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
            'index'  => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit'   => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
}