<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkOrderResource\Pages;
use App\Models\Product;
use App\Models\WorkOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Group;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Carbon\Carbon;
use Filament\Tables\Columns\ViewColumn;


class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Radni Nalozi';
    protected static ?string $pluralLabel = 'Radni Nalozi';
    protected static ?string $modelLabel = 'Radni Nalog';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('code')
            ->label('Šifra naloga')
            ->readOnly()
            ->disabled()
            ->dehydrated(false), // neće se slati jer se automatski postavlja u modelu

            TextInput::make('customer_name')
                ->label('Ime kupca')
                ->required()
                ->maxLength(255),
                

            TextInput::make('phone')
                ->label('Telefon')
                ->tel()
                ->required()
                ->maxLength(255),
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->maxLength(255)
                ->nullable(),
            Textarea::make('address')
                ->label('Adresa')
                ->rows(1)
                ->maxLength(200),
                    Select::make('status')
                ->label('Status')
                ->required()
                ->options([
                    'new' => 'Novi',
                    'in_progress' => 'U toku',
                    'done' => 'Završeno',
                    'cancelled' => 'Otkazano',
                ])
                ->default('new'),

              Select::make('type')
                ->label('Tip radnog naloga')
                ->required()
                ->options([
                    'METRAZA' => 'Metraža',
                    'GARNISNE' => 'Garnišne',
                    'ROLO' => 'Rolo zavese',
                    'ZEBRA' => 'Zebra zavese',
                    'PLISE' => 'Plise zavese',
                    'KOMARNICI' => 'Komarnici',
                    'PAKETO' => 'Paketo zavese',
                    'USLUGA' => 'Usluga',
                ])
                ->default('GARNISNE'),
                Select::make('tip_placanja')
                    ->label('Tip plaćanja')
                    ->options([
                        'KES' => 'Keš',
                        'FIRMA' => 'Firma',
                    ])
                    ->default('KES')
                    ->required(),
                 Repeater::make('positions')
                ->label('Pozicije')
                // ->relationship('positions')
                ->schema([
                    Select::make('position_type')
                        ->label('Tip pozicije')
                        ->options([
                            'metraza' => 'Metraža',
                            'garnisna' => 'Garnišna',
                        ])
                        ->reactive()
                        ->required(),

                    Select::make('product_id')
                        ->label('Proizvod')
                        ->searchable()
                        ->preload()
                        ->options(fn () => Product::pluck('name', 'id'))
                        ->reactive()
                        ->afterStateUpdated(function (Set $set, $state) {
                            $product = Product::find($state);
                            if ($product) {
                                $set('cena', $product->sale_price);
                            }
                        })
                        ->required(),

                    TextInput::make('name')
                        ->label('Naziv pozicije')
                        ->nullable(),

                    Group::make()
                        ->schema(fn (Get $get) => match ($get('position_type')) {
                            'metraza' => [
                                TextInput::make('duzina')->numeric()->label('Dužina')->default(1)->live(),
                                TextInput::make('visina')->numeric()->label('Visina'),
                                TextInput::make('nabor')->label('Nabor'),
                                TextInput::make('broj_delova')->numeric()->label('Broj delova'),
                                TextInput::make('cena')->numeric()->label('Cena')->default(0)->live(),
                            ],
                            'garnisna' => [
                                TextInput::make('duzina')->numeric()->label('Dužina')->default(1)->live(),
                                TextInput::make('cena')->numeric()->label('Cena')->default(0)->live(),
                            ],
                            default => [],
                        }),
                ])
                ->default([])
                ->live()
                ->afterStateUpdated(function (Get $get, Set $set) {
                    $positions = $get('positions') ?? [];
                    $cenaMontaze = (float) ($get('cena_montaze') ?? 0);
                    $total = $cenaMontaze;

                    foreach ($positions as $item) {
                        $duzina = isset($item['duzina']) ? (float) $item['duzina'] : 1;
                        $cena = isset($item['cena']) ? (float) $item['cena'] : 0;
                        $total += $duzina * $cena;
                    }

                    $set('total_price', $total);
                })
                ->createItemButtonLabel('Dodaj poziciju'),

           

            Textarea::make('note')
                ->label('Napomena')
                ->rows(2)
                ->nullable(),

            DateTimePicker::make('scheduled_at')
                ->label('Zakazano za'),

            TextInput::make('cena_montaze')
                ->label('Cena montaže')
                ->numeric()
                ->default(0)
                ->live(),

            TextInput::make('total_price')
                ->label('Ukupna cena')
                ->numeric()
                ->readOnly(),
        ]);
    }


        public static function table(Table $table): Table
        {
            return $table
                ->columns([
                    TextColumn::make('code')
                        ->label('Code')
                        ->badge()
                        ->color(fn ($record) => match ($record->status) {
                            'new' => 'success',
                            'in_progress' => 'warning',
                            'done' => 'blue-600',
                            'cancelled' => 'gray',
                            default => 'secondary',
                        })
                        ->searchable(),

                    SelectColumn::make('status')
                        ->label('Status')
                        ->options([
                            'new' => 'Novi',
                            'in_progress' => 'U toku',
                            'done' => 'Završeno',
                            'cancelled' => 'Otkazano',
                        ])
                        ->sortable()
                        ->searchable(),

                    TextColumn::make('customer_name')->label('Naziv')->searchable(),
                    TextColumn::make('phone')->label('Telefon'),

                    TextColumn::make('scheduled_at')
                        ->label('Zakazano')
                        ->dateTime()
                        ->color(fn ($record) => match (true) {
                            \Carbon\Carbon::parse($record->scheduled_at)->diffInDays(now(), false) > -1 => 'danger',
                            \Carbon\Carbon::parse($record->scheduled_at)->diffInDays(now(), false) >= -3 &&
                            \Carbon\Carbon::parse($record->scheduled_at)->diffInDays(now(), false) <= -1 => 'warning',
                            \Carbon\Carbon::parse($record->scheduled_at)->diffInDays(now(), false) < -3 => 'success',
                            default => null,
                        }),

                    TextColumn::make('tip_placanja')->label('Tip Plaćanja'),

                    TextColumn::make('remaining_payment')
                        ->label('Preostalo za naplatu')
                        ->money('RSD')
                        ->state(function ($record) {
                            return $record->total_price - $record->advance_payment;
                        })
                        ->weight('bold'),

                    TextColumn::make('total_price')->label('Ukupna cena')->money('RSD'),
                    TextColumn::make('advance_payment')->label('Plaćeno do sad')->money('RSD'),
                    TextColumn::make('cena_montaze')->label('Montaža')->money('RSD'),
                    TextColumn::make('note')->label('Napomena')->searchable(),
                ])
                ->defaultSort('created_at', 'desc')
                ->actions([
                Tables\Actions\Action::make('prikazi')
                    ->label('Detalji')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detalji naloga')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Zatvori')
                    ->modalContent(fn (WorkOrder $record): \Illuminate\Contracts\View\View => 
                        view('filament.resources.work-order-resource.partials.expand-row', [
                            'record' => $record,
                        ])
                    ),
                ]);

        }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkOrders::route('/'),
            'create' => Pages\CreateWorkOrder::route('/create'),
            'edit' => Pages\EditWorkOrder::route('/{record}/edit'),
        ];
    }
}
