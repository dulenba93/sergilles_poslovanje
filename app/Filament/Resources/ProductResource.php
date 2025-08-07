<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationLabel = 'Proizvodi';
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Inventar';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')
                ->label('Šifra')
                ->required(),

            Forms\Components\TextInput::make('name')
                ->label('Naziv')
                ->required(),

            Forms\Components\Select::make('vendor_id')
                ->relationship('vendor', 'name')
                ->label('Dobavljač')
                ->required(),

            Forms\Components\Textarea::make('description')
                ->label('Opis'),

            Forms\Components\TextInput::make('purchase_price')
                ->label('Nabavna cena')
                ->numeric()
                ->required(),

            Forms\Components\TextInput::make('sale_price')
                ->label('Prodajna cena')
                ->numeric()
                ->required(),

            Forms\Components\Select::make('category_id')
                ->label('Kategorija')
                ->relationship('category', 'name')
                ->required(),

            Forms\Components\TextInput::make('model_label')
                ->label('Oznaka modela'),

            Forms\Components\TextInput::make('max_height')
                ->label('Maksimalna visina')
                ->numeric(),

            Forms\Components\TextInput::make('composition')
                ->label('Sastav'),
        ]);
    }

        public static function table(Tables\Table $table): Tables\Table
        {
            return $table->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Šifra'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Naziv')
                    ->searchable(),

                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Cena')
                    ->sortable(),
                 Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Dobavljac')
                    ->sortable(),


               Tables\Columns\TextColumn::make('category.name')
                ->label('Kategorija')
                ->sortable()
                ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
        }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
