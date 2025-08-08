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
        <div class="flex gap-2">
            <x-filament::button tag="a"
                href="{{ route('work-orders.export.pdf', $record) }}"
                target="_blank">
                Export PDF
            </x-filament::button>

            <x-filament::button tag="a"
                href="{{ route('work-orders.export.excel', $record) }}"
                target="_blank"
                color="info">
                Export Excel
            </x-filament::button>
        </div>


</div>
