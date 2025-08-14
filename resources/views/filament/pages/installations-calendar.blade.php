<x-filament::page>
    <style>
        .toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .btn { padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; cursor: pointer; }
        .btn:hover { background: #f9fafb; }
        .muted { color: #6b7280; }
        .missing-card {
            border: 1px solid #f59e0b; background: #fffbeb; color: #92400e;
            padding: 8px 10px; border-radius: 10px; margin-bottom: 12px;
        }

        /* UNSCHEDULED POOL */
        .unscheduled-wrap { margin: 12px 0 16px; }
        .unscheduled-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; }
        .unscheduled-pool {
            display: grid;
            grid-template-columns: repeat( auto-fill, minmax(240px, 1fr) );
            gap: 8px;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 10px;
            background: #f8fafc;
            min-height: 56px;
        }
        .unscheduled-pool.drag-over { background: #eef6ff; border-color: #93c5fd; }

        table.calendar { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.calendar th, table.calendar td { border: 1px solid #e5e7eb; vertical-align: top; padding: 8px; }
        table.calendar th { background: #f9fafb; font-weight: 600; text-align: left; }
        .badge { font-size: 11px; padding: 1px 6px; border-radius: 999px; border: 1px solid #e5e7eb; background: #fff; }
        .event {
            border-radius: 8px; padding: 6px 8px; color: #111; border: 1px solid #e5e7eb; background: #f8fafc;
            font-size: 12px; text-decoration: none; display: block; text-align: left;
        }
        .event + .event { margin-top: 6px; }

        /* Status colors */
        .status-new        { background: #dcfce7; border-color: #86efac; }
        .status-inprogress { background: #fef9c3; border-color: #fde68a; }
        .status-done       { background: #f3f4f6; border-color: #e5e7eb; }
        .status-cancelled  { background: #fee2e2; border-color: #fca5a5; }

        .nowrap { white-space: nowrap; }
        .head-flex { display: flex; justify-content: space-between; align-items: center; }
        .dim { color:#6b7280; font-size:11px; }

        /* Modal width */
        .modal-wide .fi-modal-window { max-width: 1100px; width: 90vw; }

        /* Drag & drop hints */
        .drop-target { transition: background-color .15s ease; min-height: 40px; }
        .drop-target.drag-over { background-color: #f0f9ff; }
        .event.dragging { opacity: .6; }
    </style>

    @php
        [$start, $end] = $this->getWeekRange();
        $days        = $this->getWeekDays();
        $events      = $this->getEvents();
        $missing     = $this->getMissingCount();
        $unscheduled = $this->getUnscheduledOrders(); // NOVO

        $srDays = ['Ponedeljak','Utorak','Sreda','Četvrtak','Petak','Subota','Nedelja'];

        $statusClass = function(string $s): string {
            return \App\Filament\Pages\InstallationsCalendar::statusColor($s);
        };

        $typeLabel = fn(string $t) => match($t) {
            'METRAZA' => 'Metraža', 'GARNISNE' => 'Garnišne', 'ROLO' => 'Rolo',
            'ZEBRA' => 'Zebra', 'PLISE' => 'Plise', 'KOMARNICI' => 'Komarnici', 'USLUGA' => 'Usluga',
            default => $t,
        };
    @endphp

    <div class="toolbar">
        <div>
            <button type="button" wire:click="prevWeek" class="btn">← Nedelja</button>
            <button type="button" wire:click="nextWeek" class="btn">Nedelja →</button>
        </div>
        <div class="muted nowrap">
            Nedelja: {{ $start->format('d.m.Y') }} — {{ $end->format('d.m.Y') }}
        </div>
        <div>
        <a
            class="btn"
            href="{{ route('work-orders.week-pdf', ['week_start' => $start->toDateString()]) }}"
            target="_blank"
            title="Export u PDF"
        >
            Export kalendara montazi (PDF)
        </a>
    </div>
    </div>

    <div class="missing-card">
        Nalozi bez datuma montaže (status <strong>novi</strong> ili <strong>u toku</strong>): <strong>{{ $missing }}</strong>
    </div>

    {{-- UNSCHEDULED POOL --}}
    <div class="unscheduled-wrap">
        <div class="unscheduled-head">
            <div class="muted">Neraspoređene montaže — prevuci na dan u kalendaru da zakažeš</div>
            <div class="badge">{{ $unscheduled->count() }} kom</div>
        </div>

        <div
            class="unscheduled-pool drop-target"
            x-data
            @dragover.prevent
            @dragenter.prevent="$el.classList.add('drag-over')"
            @dragleave.prevent="$el.classList.remove('drag-over')"
            @drop.prevent="
                $el.classList.remove('drag-over');
                try {
                    const data = JSON.parse(event.dataTransfer.getData('text/plain') || '{}');
                    if (data && data.id) { $wire.unscheduleOrder(data.id); }
                } catch(e) {}
            "
            title="Prevuci ovde da ukloniš datum (vratiš u neraspoređene)"
        >
            @forelse ($unscheduled as $o)
                @php $cls = $statusClass($o->status); @endphp
                <button
                    type="button"
                    class="event {{ $cls }}"
                    wire:click="openOrder({{ $o->id }})"
                    draggable="true"
                    x-data
                    @dragstart="
                        event.dataTransfer.setData('text/plain', JSON.stringify({ id: {{ $o->id }} }));
                        event.dataTransfer.effectAllowed = 'move';
                        $el.classList.add('dragging');
                    "
                    @dragend="$el.classList.remove('dragging')"
                >
                    <div><strong>#{{ $o->code }}</strong> — {{ $o->customer_name }}</div>
                    <div class="dim">Tip: {{ $typeLabel($o->type ?? 'USLUGA') }}</div>
                    <div class="dim">  Tel: {{ $o->phone }}</div>
                    <div class="dim">Adresa: {{ $o->address ?: '—' }}</div>
                    <div class="dim">
                        Preostalo: {{
                            number_format( (float)($o->total_price ?? 0) - (float)($o->advance_payment ?? 0), 2, ',', '.')
                        }} RSD
                    </div>
                </button>
            @empty
                <div class="dim">Nema neraspoređenih naloga.</div>
            @endforelse
        </div>
    </div>

    {{-- KALENDAR --}}
    <table class="calendar">
        <thead>
            <tr>
                @foreach ($days as $i => $day)
                    <th>
                        <div class="head-flex">
                            <span>{{ $srDays[$i] }}</span>
                            <span class="badge">{{ $day->format('d.m.') }}</span>
                        </div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                @foreach ($days as $day)
                    @php
                        $key   = $day->toDateString();
                        $items = $events[$key] ?? [];
                    @endphp

                    <td
                        x-data
                        class="drop-target"
                        @dragover.prevent
                        @dragenter.prevent="$el.classList.add('drag-over')"
                        @dragleave.prevent="$el.classList.remove('drag-over')"
                        @drop.prevent="
                            $el.classList.remove('drag-over');
                            try {
                                const data = JSON.parse(event.dataTransfer.getData('text/plain') || '{}');
                                if (data && data.id) { $wire.moveOrder(data.id, '{{ $day->toDateString() }}'); }
                            } catch(e) {}
                        "
                    >
                        @forelse ($items as $o)
                            @php $cls = $statusClass($o->status); @endphp
                            <button
                                type="button"
                                class="event {{ $cls }}"
                                wire:click="openOrder({{ $o->id }})"
                                draggable="true"
                                x-data
                                @dragstart="
                                    event.dataTransfer.setData('text/plain', JSON.stringify({ id: {{ $o->id }} }));
                                    event.dataTransfer.effectAllowed = 'move';
                                    $el.classList.add('dragging');
                                "
                                @dragend="$el.classList.remove('dragging')"
                                title="Prevuci na drugi dan da promeniš datum"
                            >
                                <div><strong>#{{ $o->code }}</strong> — {{ $o->customer_name }}</div>
                                <div class="dim">Tip: {{ $typeLabel($o->type ?? 'USLUGA') }} • Tel: {{ $o->phone }}</div>
                                <div class="dim">Status: {{ $o->status }}</div>
                            </button>
                        @empty
                            <div class="dim">Nema zakazanih.</div>
                        @endforelse
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>

    {{-- MODAL --}}
    <x-filament::modal
        id="work-order-modal"
        class="modal-wide"
        width="7xl"
        :close-by-clicking-away="true"
        :close-by-escaping="true"
        alignment="center"
    >
        <x-slot name="header">
            <div style="font-weight:600">
                @if($modalOrder)
                    Radni nalog #{{ $modalOrder->code }} — {{ $modalOrder->customer_name }}
                @else
                    Radni nalog
                @endif
            </div>
        </x-slot>

        <div wire:key="work-order-modal-{{ optional($modalOrder)->id ?? 'empty' }}">
            @if ($modalOrder)
                @include('filament.resources.work-order-resource.partials.expand-row', [
                    'record' => $modalOrder,
                ])
            @endif
        </div>

        <x-slot name="footer">
            <x-filament::button color="gray" wire:click="closeModal">Zatvori</x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament::page>
