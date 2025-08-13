<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Product;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Collection;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Prodaje';
    protected static ?string $navigationGroup = 'ULAZ';
    protected static ?int $navigationSort = 20;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Osnovno')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Šifra')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('type')
                            ->label('Tip prodaje')
                            ->options([
                                'GARNISNE'  => 'Garnisne',
                                'METRAZA'   => 'Metraža',
                                'ROLO'      => 'Rolo',
                                'ZEBRA'     => 'Zebra',
                                'PLISE'     => 'Plise',
                                'KOMARNICI' => 'Komarnici',
                                'USLUGA'    => 'Usluga',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                $set('unit', \App\Models\Sale::unitForType($state));
                            }),

                        Forms\Components\Select::make('product_id')
                            ->label('Artikal')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if ($state) {
                                    $product = Product::find($state);
                                    if ($product) {
                                        $set('unit_price', $product->sale_price);
                                        $quantity = (float) $get('quantity') ?: 1;
                                        $set('total_price', $quantity * (float) $product->sale_price);
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('unit')
                            ->label('Jedinica')
                            ->disabled()
                            ->required(),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Količina')
                            ->numeric()
                            ->default(1)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                $unitPrice = (float) ($get('unit_price') ?? 0);
                                if ($unitPrice > 0) {
                                    $set('total_price', (float) $state * $unitPrice);
                                }
                            })
                            ->required(),

                        Forms\Components\TextInput::make('unit_price')
                            ->label('Cena po jedinici')
                            ->numeric()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                $qty = (float) ($get('quantity') ?? 1);
                                $set('total_price', $qty * (float) $state);
                            }),

                        Forms\Components\TextInput::make('total_price')
                            ->label('Ukupna cena')
                            ->numeric()
                            ->helperText('Automatski: cena × količina, ali možete ručno izmeniti.'),

                        Forms\Components\TextInput::make('paid_amount')
                            ->label('Plaćeno do sad')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Select::make('payment_type')
                            ->label('Tip plaćanja')
                            ->options([
                                'KES'   => 'Keš',
                                'FIRMA' => 'Firma',
                            ])
                            ->required(),

                        Forms\Components\Select::make('status') // DODATO
                            ->label('Status')
                            ->options([
                                'new'         => 'Novi',
                                'in_progress' => 'U toku',
                                'done'        => 'Završen',
                            ])
                            ->default('new')
                            ->required(),

                        Forms\Components\TextInput::make('customer_description')
                            ->label('Kupac / opis')
                            ->maxLength(255),
                    ])->columns(3),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Šifra')->searchable(),
                Tables\Columns\TextColumn::make('product.name')->label('Artikal')->limit(20)->toggleable(),
                Tables\Columns\TextColumn::make('type')->label('Tip')->badge(),
                Tables\Columns\TextColumn::make('unit')->label('JM'),
                Tables\Columns\TextColumn::make('quantity')->label('Količina'),
                Tables\Columns\TextColumn::make('unit_price')->label('Cena/JM')->money('rsd', true),
                Tables\Columns\TextColumn::make('total_price')->label('Ukupno')->money('rsd', true)->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')->label('Plaćeno')->money('rsd', true),
                Tables\Columns\TextColumn::make('payment_type')->label('Plaćanje')->badge(),

                // INLINE EDIT status preko SelectColumn:
                Tables\Columns\SelectColumn::make('status')->label('Status')
                    ->options([
                        'new'         => 'Novi',
                        'in_progress' => 'U toku',
                        'done'        => 'Završen',
                    ])
                    ->selectablePlaceholder(false)
                    ->rules(['in:new,in_progress,done']),

                Tables\Columns\TextColumn::make('customer_description')->label('Kupac / opis')->limit(30),
                Tables\Columns\TextColumn::make('created_at')->label('Datum')->dateTime('d.m.Y H:i'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export_pdf')
                    ->label('Export PDF (selektovano)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    ->action(function (Collection $records) {
                        $ids = $records->pluck('id')->implode(',');
                        return redirect()->route('sales.export.pdf', ['ids' => $ids]);
                    }),

                Tables\Actions\BulkAction::make('export_excel')
                    ->label('Export Excel (selektovano)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (Collection $records) {
                        $ids = $records->pluck('id')->implode(',');
                        return redirect()->route('sales.export.excel', ['ids' => $ids]);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit'   => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}