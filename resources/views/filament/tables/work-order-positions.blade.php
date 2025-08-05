<div class="p-4 space-y-4">
    <h3 class="text-lg font-semibold">Pozicije</h3>

    @forelse ($record->positions as $position)
        <div class="border p-3 rounded bg-gray-50">
            <p><strong>Tip:</strong> {{ strtoupper($position->pozicija_type) }}</p>
            <p><strong>Naziv:</strong> {{ $position->naziv ?? '-' }}</p>
            <p><strong>Napomena:</strong> {{ $position->napomena ?? '-' }}</p>
            <p><strong>Detalji:</strong>
                @if($position->pozicija_type === 'metraza' && $position->metraza)
                    Dužina: {{ $position->metraza->duzina }} | 
                    Visina: {{ $position->metraza->visina }} | 
                    Nabor: {{ $position->metraza->nabor }} | 
                    Delova: {{ $position->metraza->broj_delova }} | 
                    Cena: {{ number_format($position->metraza->cena, 2) }} RSD
                @elseif($position->pozicija_type === 'garnisna' && $position->garnisna)
                    Dužina: {{ $position->garnisna->duzina }} |
                    Cena: {{ number_format($position->garnisna->cena, 2) }} RSD
                @else
                    Nema dodatnih podataka
                @endif
            </p>
        </div>
    @empty
        <p>Nema pozicija.</p>
    @endforelse
</div>
