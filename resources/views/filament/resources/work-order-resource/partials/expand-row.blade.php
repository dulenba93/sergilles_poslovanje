<div class="space-y-6">
    <div class="text-lg font-bold">Radni nalog #{{ $record->code }}</div>
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
    <div><strong>Ime kupca:</strong> {{ $record->customer_name }}</div>
    <div><strong>Telefon:</strong> {{ $record->phone }}</div>
    <div><strong>Email:</strong> {{ $record->email ?? '-' }}</div>

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

    <div>
        <strong>Napomena:</strong><br>
        <div class="">{{ $record->note ?? '—' }}</div>
    </div>
    <hr>
@if($record->positions && $record->positions->count())
    <div>
        <h3 class="text-lg font-bold mb-2">Pozicije</h3>

        @foreach ($record->positions as $index => $pozicija)
            @php
                $type = $pozicija->pozicija_type;
                $model = null;

                if ($type === 'metraza') {
                    $model = \App\Models\PozicijaMetraza::find($pozicija->pozicija_id);
                } elseif ($type === 'garnisna') {
                    $model = \App\Models\PozicijaGarnisna::find($pozicija->pozicija_id);
                }

                $product = $model?->product;
            @endphp

            @if ($model)
                <div class="mb-4 border border-gray-200 rounded p-4">
                    <div class="font-semibold mb-2">
                     {{ $index + 1 }} -> {{ $model->name ?? 'Bez naziva' }} ({{ strtoupper($type) }})
                    </div>

                    <ul class="list-disc list-inside text-sm text-gray-800 space-y-1">
                                   @if ($product)
                            <li><strong>Proizvod:</strong> {{ $product->name }}  {{ $product->opis }}</li>
                        @else
                            <li><strong>Proizvod:</strong> Nepoznat</li>
                        @endif
                        @foreach ($model->getAttributes() as $key => $value)
                            @continue(in_array($key, ['id', 'created_at', 'updated_at', 'product_id','name']))
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
        <x-filament::button color="gray">Pošalji potvrdu</x-filament::button>
        <x-filament::button color="success">Sačuvaj nalog</x-filament::button>
        <x-filament::button color="info">Pogledaj pozicije</x-filament::button>
    </div>
</div>
