<div class="space-y-4">
    <div class="text-lg font-bold">Radni nalog #{{ $record->code }}</div>

    <div class="grid grid-cols-3 gap-4">
        <div><strong>Ime kupca:</strong> {{ $record->customer_name }}</div>
        <div><strong>Telefon:</strong> {{ $record->phone }}</div>
        <div><strong>Email:</strong> {{ $record->email }}</div>

        <div><strong>Tip plaćanja:</strong> {{ $record->tip_placanja }}</div>
        <div><strong>Status:</strong> {{ $record->status }}</div>
        <div><strong>Zakazano:</strong>
            {{ $record->scheduled_at ? \Carbon\Carbon::parse($record->scheduled_at)->format('d.m.Y H:i') : '-' }}
        </div>

        <div><strong>Ukupna cena:</strong> {{ number_format($record->total_price, 2, ',', '.') }} RSD</div>
        <div><strong>Avans:</strong> {{ number_format($record->advance_payment, 2, ',', '.') }} RSD</div>
        <div><strong>Preostalo za naplatu:</strong> 
            {{ number_format($record->total_price - $record->advance_payment, 2, ',', '.') }} RSD
        </div>
    </div>

    <div class="mt-6">
        <strong>Napomena:</strong><br>
        <div class="">{{ $record->note }}</div>
    </div>

    <hr class="my-6" />

    @if ($record->workOrderPositions && count($record->workOrderPositions) > 0)
        <h3 class="text-lg font-bold mb-2">Pozicije</h3>

        @foreach ($record->workOrderPositions as $index => $position)
            @php
                $pozicija = $position->metraza ?? $position->garnisna;
                $tip = $position->metraza ? 'Metraža' : ($position->garnisna ? 'Garnišna' : null);
            @endphp

            @if ($pozicija)
                <div class="mb-4 border rounded p-4 bg-gray-50">
                    <div class="font-semibold mb-2">
                        Pozicija {{ $index + 1 }} - {{ $pozicija->name ?? 'Bez naziva' }} ({{ $tip }})
                    </div>

                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($pozicija->getAttributes() as $key => $value)
                            @continue(in_array($key, ['id', 'created_at', 'updated_at', 'name']))
                            <li>
                                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                {{ is_numeric($value) ? number_format($value, 2, ',', '.') : $value }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach
    @else
        <p class="text-gray-500 italic">Nema dodatih  za ovaj nalog.</p>
    @endif

    <div class="flex gap-2 mt-6">
        <x-filament::button color="gray">Pošalji potvrdu</x-filament::button>
        <x-filament::button color="success">Sačuvaj nalog</x-filament::button>
        <x-filament::button color="info">Pogledaj pozicije</x-filament::button>
    </div>
</div>
