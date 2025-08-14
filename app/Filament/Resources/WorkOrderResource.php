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
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Carbon\Carbon;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Actions\BulkAction;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SelectedPositionsExport;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;


class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Radni Nalozi';
    protected static ?string $pluralLabel = 'Radni Nalozi';
    protected static ?string $modelLabel = 'Radni Nalog';
    protected static ?string $navigationGroup = 'ULAZ';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('code')
                ->label('Šifra naloga')
                ->readOnly()
                ->disabled()
                ->dehydrated(false),

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
                    'METRAZA'   => 'Metraža',
                    'GARNISNE'  => 'Garnišne',
                    'ROLO'      => 'Rolo zavese',
                    'ZEBRA'     => 'Zebra zavese',
                    'PLISE'     => 'Plise zavese',
                    'KOMARNICI' => 'Komarnici',
                    'PAKETO'    => 'Paketo zavese',
                    'USLUGA'    => 'Usluga',
                ])
                ->default('GARNISNE'),

            Select::make('tip_placanja')
                ->label('Tip plaćanja')
                ->options([
                    'KES'   => 'Keš',
                    'FIRMA' => 'Firma',
                ])
                ->default('KES')
                ->required(),
                Repeater::make('positions')
                    ->label('Pozicije')
                    ->schema([
                        Select::make('position_type')
                            ->label('Tip pozicije')
                              ->options([
                                    'metraza'    => 'Metraža',
                                    'garnisna'   => 'Garnišna',
                                    'rolo_zebra' => 'Rolo/Zebra',
                                    'plise'      => 'Plise',
                                ])
                            ->reactive()
                            ->required(),

                        // Proizvod i model u istom redu
                        Grid::make(2)->schema([
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
                            TextInput::make('model')
                                ->label('Model')
                                ->nullable(), // samo model NIJE required
                        ]),

                        TextInput::make('name')
                            ->label('Naziv pozicije')
                            ->required(),

                        // Dinamička polja po tipu pozicije
                        Group::make()
                            ->schema(fn (Get $get) => match ($get('position_type')) {
                                'metraza' => [
                                    // Dužina, visina, nabor, broj delova u istom redu
                                    Grid::make(4)->schema([
                                        TextInput::make('duzina')->numeric()->label('Dužina u m')->default(1)->required(),
                                        TextInput::make('visina')->numeric()->label('Visina u cm')->required(),
                                        TextInput::make('nabor')->label('Nabor')->required(),
                                        TextInput::make('broj_delova')->numeric()->label('Broj delova')->required(),
                                    ]),
                                    // Cena i broj komada u istom redu
                                    Grid::make(2)->schema([
                                        TextInput::make('cena')->numeric()->label('Cena')->default(0)->required(),
                                        TextInput::make('broj_kom')->default(1)->required()
                                            ->numeric()
                                            ->label('Broj komada')
                                            ->default(1)
                                            ->minValue(1)
                                            ->required(),
                                    ]),
                                ],
                                'garnisna' => [
                                    // Dužina i broj komada u istom redu
                                    Grid::make(2)->schema([
                                        TextInput::make('duzina')->numeric()->label('Dužina u m')->default(1)->required(),
                                        TextInput::make('broj_kom')->default(1)->required()
                                            ->numeric()
                                            ->label('Broj komada')
                                            ->default(1)
                                            ->minValue(1)
                                            ->required(),
                                    ]),
                                    // Cena i model u istom redu
                                    Grid::make(2)->schema([
                                        TextInput::make('cena')->numeric()->label('Cena')->default(0)->required(),
                                    ]),
                                ],
                               'rolo_zebra' => [
                                // sirina i visina (m), plus izbor da li se sirina odnosi na mehanizam ili platno
                                Grid::make(3)->schema([
                                    TextInput::make('sirina')
                                        ->numeric()
                                        ->label('Širina (m)')
                                        ->required(),
                                    TextInput::make('visina')
                                        ->numeric()
                                        ->label('Visina (m)')
                                        ->required(),
                                    Select::make('sirina_type')
                                        ->label('Širina se odnosi na')
                                        ->options([
                                            'mehanizam' => 'Mehanizam',
                                            'platno'    => 'Platno',
                                        ])
                                        ->default('mehanizam')
                                        ->required(),
                                ]),
                                // odabir mehanizma, način kačenja i pravac potezanja
                                Grid::make(3)->schema([
                                    Select::make('mehanizam')
                                        ->label('Mehanizam')
                                        ->options(['mini' => 'Mini', 'standard' => 'Standard'])
                                        ->default('standard')
                                        ->required(),
                                    Select::make('kacenje')
                                        ->label('Kačenje')
                                        ->options([
                                            'plafon'       => 'Plafon',
                                            'zid'          => 'Zid',
                                            'pvc_kacenje' => 'PVC kačenje',
                                        ])
                                        ->default('plafon')
                                        ->required(),
                                    Select::make('potez')
                                        ->label('Potez')
                                        ->options(['levo' => 'Levo', 'desno' => 'Desno'])
                                        ->default('levo')
                                        ->required(),
                                ]),
                                // cena, broj komada, maska / boja
                                Grid::make(3)->schema([
                                    TextInput::make('cena')
                                        ->numeric()
                                        ->label('Cena')
                                        ->default(0)
                                        ->required(),
                                    TextInput::make('broj_kom')
                                        ->numeric()
                                        ->label('Broj komada')
                                        ->default(1)
                                        ->minValue(1)
                                        ->required(),
                                    TextInput::make('maska_boja')
                                        ->label('Maska / boja')
                                        ->nullable(),
                                ]),
                            ],
                               'plise' => [
                                // sirina i visina u centimetrima
                                Grid::make(2)->schema([
                                    TextInput::make('sirina')
                                        ->numeric()
                                        ->label('Širina (cm)')
                                        ->default(1)
                                        ->required(),
                                    TextInput::make('visina')
                                        ->numeric()
                                        ->label('Visina (cm)')
                                        ->required(),
                                ]),
                                // mehanizam i potez
                                Grid::make(2)->schema([
                                    Select::make('mehanizam')
                                        ->label('Mehanizam')
                                        ->options(['standard' => 'Standard', 'zabice' => 'Zabice', 'lepljenje' => 'Lepljenje'])
                                        ->default('standard')
                                        ->required(),
                                    Select::make('potez')
                                        ->label('Potez')
                                        ->options(['levo' => 'Levo', 'desno' => 'Desno'])
                                        ->default('levo')
                                        ->required(),
                                ]),
                                // cena, broj komada, maska / boja
                                Grid::make(3)->schema([
                                    TextInput::make('cena')
                                        ->numeric()
                                        ->label('Cena')
                                        ->default(0)
                                        ->required(),
                                    TextInput::make('broj_kom')
                                        ->numeric()
                                        ->label('Broj komada')
                                        ->default(1)
                                        ->minValue(1)
                                        ->required(),
                                    TextInput::make('maska_boja')
                                        ->label('Maska / boja')
                                        ->nullable(),
                                ]),
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
                            $cena   = isset($item['cena']) ? (float) $item['cena'] : 0;
                            $br_kom = isset($item['broj_kom']) ? (float) $item['broj_kom'] : 1;
                            $type   = $item['position_type'] ?? null;

                            if (in_array($type, ['metraza', 'garnisna'])) {
                                $duzina = isset($item['duzina']) ? (float) $item['duzina'] : 1;
                                $total += $duzina * $cena * $br_kom;
                            } elseif ($type === 'plise') {
                                // širina i visina su u centimetrima → preračun u m²
                                $sirina   = isset($item['sirina']) ? (float) $item['sirina'] : 0;
                                $visina   = isset($item['visina']) ? (float) $item['visina'] : 0;
                                $povrsina = ($sirina * $visina) / 10000; // cm² → m²
                                if ($povrsina < 1) {
                                    $povrsina = 1;
                                }
                                $total += $povrsina * $cena * $br_kom;
                            } elseif ($type === 'rolo_zebra') {
                                // širina i visina u metrima, površina u m²
                                $sirina   = isset($item['sirina']) ? (float) $item['sirina'] : 0;
                                $visina   = isset($item['visina']) ? (float) $item['visina'] : 0;
                                $povrsina = $sirina * $visina;
                                if ($povrsina < 1) {
                                    $povrsina = 1;
                                }
                                $total += $povrsina * $cena * $br_kom;
                            } else {
                                // fallback: cena po komadu
                                $total += $cena * $br_kom;
                            }
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

                                TextInput::make('advance_payment')
                                    ->label('Avans/Placeno')
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
                                        
                                    Tables\Columns\TextColumn::make('type')
                                        ->label('Tip')
                                        ->sortable()
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
                                            TextColumn::make('note')->label('Napomena')->limit(30)->searchable(),
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
                                        ])
                                           ->bulkActions([
                                                    BulkAction::make('exportSelectedExcel')
                                                        ->label('Export po poziciji (Excel)')
                                                        ->icon('heroicon-o-arrow-down-tray')
                                                        ->requiresConfirmation() // da korisnik potvrdi
                                                        ->action(function (EloquentCollection $records) {
                                                            // $records is Eloquent\Collection of WorkOrder models

                                                            if ($records->isEmpty()) {
                                                                Notification::make()
                                                                    ->title('Nije izabrano ništa')
                                                                    ->warning()
                                                                    ->send();
                                                                return;
                                                            }

                                                            $ids = $records->pluck('id')->all();
                                                            $fileName = 'Pozicije-selekcija-' . now()->format('Ymd-His') . '.xlsx';

                                                            // Return download response
                                                            return Excel::download(new SelectedPositionsExport($ids), $fileName);
                                                        }),
                                            ]);
                                }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWorkOrders::route('/'),
            'create' => Pages\CreateWorkOrder::route('/create'),
            'edit'   => Pages\EditWorkOrder::route('/{record}/edit'),
        ];
    }
}
