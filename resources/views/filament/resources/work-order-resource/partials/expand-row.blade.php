<div class="space-y-6">
    <div class="text-lg font-bold">Radni nalog #{{ $record->code }}</div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
        <div><strong>Ime kupca:</strong> {{ $record->customer_name }}</div>

        <div>
            <strong>Telefon:</strong>
            @if ($record->phone)
                <a href="tel:{{ preg_replace('/\s+/', '', $record->phone) }}" class="hover:underline">
                    {{ $record->phone }}
                </a>
            @else
                -
            @endif
        </div>

        <div><strong>Email:</strong> {{ $record->email ?? '-' }}</div>

        <div><strong>Tip plaćanja:</strong> {{ $record->tip_placanja }}</div>
            <div>
                <strong>Status:</strong>
                @php
                    $statusStyles = match ($record->status) {
                        'new'    => 'background-color: #1f8a16ff; color: #000; padding: 2px 6px; border-radius: 4px;', // žuta
                        'in_progress'  => 'background-color: #e8eb0cff; color: #fff; padding: 2px 6px; border-radius: 4px;', // plava
                        'done' => 'background-color: #969796ff; color: #fff; padding: 2px 6px; border-radius: 4px;', // zelena
                        'cancelled' => 'background-color: #ef4444; color: #fff; padding: 2px 6px; border-radius: 4px;', // crvena
                        default   => 'background-color: #9ca3af; color: #fff; padding: 2px 6px; border-radius: 4px;', // siva
                    };
                @endphp
                <span style="{{ $statusStyles }}">
                    {{ ucfirst(str_replace('_', ' ', $record->status ?? '-')) }}
                </span>
            </div>



        <div>
            <strong>Zakazano:</strong>
            {{ $record->scheduled_at ? \Carbon\Carbon::parse($record->scheduled_at)->format('d.m.Y') : '-' }}
        </div>

        <div><strong>Ukupna cena:</strong> {{ number_format($record->total_price, 2, ',', '.') }} RSD</div>
        <div><strong>Avans:</strong> {{ number_format($record->advance_payment, 2, ',', '.') }} RSD</div>
        <div>
            <strong>Preostalo za naplatu:</strong>
            {{ number_format(($record->total_price ?? 0) - ($record->advance_payment ?? 0), 2, ',', '.') }} RSD
        </div>
    </div>

    <div>
        <strong>Napomena:</strong><br>
        <div>{{ $record->note ?? '—' }}</div>
    </div>

    <hr>

    @if ($record->positions && $record->positions->count())
        <div>
            <h3 class="text-lg font-bold mb-2">Pozicije</h3>

            @foreach ($record->positions as $index => $pozicija)
                @php
                    $type = $pozicija->pozicija_type;
                    $model = match ($type) {
                        'metraza'    => \App\Models\PozicijaMetraza::find($pozicija->pozicija_id),
                        'garnisna'   => \App\Models\PozicijaGarnisna::find($pozicija->pozicija_id),
                        'rolo_zebra' => \App\Models\PozicijaRoloZebra::find($pozicija->pozicija_id),
                        'plise'      => \App\Models\PozicijaPlise::find($pozicija->pozicija_id),
                        default      => null,
                    };
                    $product = $model?->product;
                @endphp

                @if ($model)
                    <div class="mb-4 border border-gray-200 rounded p-4">
                        <div class="font-semibold mb-2">
                            {{ $index + 1 }} -> {{ $model->name ?? 'Bez naziva' }} ({{ strtoupper($type) }})
                        </div>

                        <ul class="list-disc list-inside text-sm text-gray-800 space-y-1">
                            @if ($product)
                                <li><strong>Proizvod:</strong> {{ $product->name }} {{ $product->opis }}</li>
                            @else
                                <li><strong>Proizvod:</strong> Nepoznat</li>
                            @endif

                            @foreach ($model->getAttributes() as $key => $value)
                                @continue(in_array($key, ['id', 'created_at', 'updated_at', 'product_id', 'name']))
                                <li>
                                    <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <p class="text-gray-500 italic">Nema dodatih pozicija za ovaj nalog.</p>
    @endif
 

        {{-- ==== EXPORT PDF sa izborom računa + PDV ==== --}}
                <div x-data="{
                        open:false,
                        racun:'firma',
                        pdv_included:false,
                        submit() {
                            const params = new URLSearchParams({
                                racun: this.racun,                    // 'firma' ili 'dusan'
                                pdv_included: this.pdv_included ? 1 : 0,
                            });
                            const url = '{{ route('work-orders.proforma-pdf', $record) }}' + '?' + params.toString();
                            window.open(url, '_blank');              // otvori generisani PDF u novom tabu
                            this.open = false;
                        }
                    }"
                    class="mt-3"
                >
                    <x-filament::button color="success" x-on:click="open = true">
                        Export Profaktura
                    </x-filament::button>

                    {{-- MODAL --}}
                    <div
                        x-cloak
                        x-show="open"
                        x-transition.opacity
                        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50"
                    >
                        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                            <div class="text-lg font-semibold mb-4">Opcije izvoza u PDF</div>

                            <div class="space-y-5">
                                {{-- Račun --}}
                                <div>
                                    <div class="text-sm font-medium mb-2">Izaberite račun za profakturu:</div>
                                    <label class="flex items-center gap-2 mb-2">
                                        <input type="radio" name="racun" value="firma" x-model="racun" class="fi-radio">
                                        <span>Firma — <span class="font-mono">265-6240310000065-53</span></span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="racun" value="dusan" x-model="racun" class="fi-radio">
                                        <span>Dušan — <span class="font-mono">115-0000000066773-50</span></span>
                                    </label>
                                </div>

                                {{-- PDV --}}
                                <div>
                                    <div class="text-sm font-medium mb-2">PDV opcija:</div>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" x-model="pdv_included" class="fi-checkbox">
                                        <span>PDV je uračunat u cenama artikala (20%)</span>
                                    </label>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Ako je čekirano, trenutne cene se tretiraju kao <em>bruto</em>; osnovica = cena / 1.2.
                                        Ako nije, cene su <em>neto</em> i PDV se obračunava kao i do sada.
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <x-filament::button color="gray" x-on:click="open=false">Otkaži</x-filament::button>
                                <x-filament::button color="primary" x-on:click="submit()">Nastavi</x-filament::button>
                            </div>
                        </div>
                    </div>
                </div>
        <x-filament::button tag="a" color="danger" href="{{ route('work-orders.pdf', $record) }}" target="_blank">
            Export Nalog PDF
        </x-filament::button>

        <x-filament::button tag="a" color="warning" href="{{ route('work-orders.proforma', $record) }}" target="_blank">
            Export Nalog (Excel)
        </x-filament::button>


</div>
