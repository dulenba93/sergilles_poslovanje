<x-filament::page>
    <style>
        /* basic layout without Tailwind */
        .dash-wrap { width: 100%; }
        .mb-4 { margin-bottom: 16px; }
        .mb-6 { margin-bottom: 24px; }
        .cards-row {
            display: flex;
            flex-wrap: nowrap;
            gap: 12px;
        }
        .card {
            flex: 1 1 0;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px;
            background: #fff;
            min-width: 0;
        }
        .card .label { font-size: 12px; color: #6b7280; margin-bottom: 6px; }
        .card .value { font-size: 22px; font-weight: 700; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .charts-row {
            display: flex;
            flex-wrap: nowrap;
            gap: 12px;
        }
        .chart-half {
            flex: 0 0 50%;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px;
            background: #fff;
            min-width: 0;
        }
        .chart-title { font-weight: 600; margin-bottom: 8px; }

        .actions-bottom {
            display: flex;
            gap: 8px;
        }
        /* filament buttons already styled; wrapper ensures placement */
    </style>

    {{-- FILTER FORMA (ostaje kako je) --}}
    <form wire:submit.prevent="submitFiltersForm" class="mb-4">
        {{ $this->filtersForm }}
    </form>

    @php
        [$fromCarbon, $toCarbon] = $this->getRange();
        $metrics = $this->getMetrics();
        $fmt = fn($n) => number_format($n, 2, ',', '.');

        $statusLabelsMap = ['new' => 'Novi', 'in_progress' => 'U toku', 'done' => 'Završen', 'cancelled' => 'Otkazan'];

        $statusLabels = [];
        $statusValues = [];
        foreach ($statusLabelsMap as $k => $label) {
            $statusLabels[] = $label;
            $statusValues[] = (int) ($metrics['statusCounts'][$k] ?? 0);
        }

        $typeLabels = array_values(array_map('strval', array_keys($metrics['typeShare'])));
        $typeValues = array_values(array_map('floatval', array_values($metrics['typeShare'])));
    @endphp

    <div class="dash-wrap">

        {{-- RED 1: 5 kartica u jednom redu --}}
        <div class="cards-row mb-6">
            <div class="card">
                <div class="label">Broj porudžbina</div>
                <div class="value">{{ $metrics['totalOrders'] }}</div>
            </div>
            <div class="card">
                <div class="label">Ukupan očekivan novac</div>
                <div class="value">{{ $fmt($metrics['expected']) }} RSD</div>
            </div>
            <div class="card">
                <div class="label">Ukupno naplaćeno</div>
                <div class="value">{{ $fmt($metrics['paid']) }} RSD</div>
            </div>
            <div class="card">
                <div class="label">Preostalo za naplatu</div>
                <div class="value">{{ $fmt($metrics['remaining']) }} RSD</div>
            </div>
            <div class="card">
                <div class="label">Ukupno za montaže</div>
                <div class="value">{{ $fmt($metrics['montaze']) }} RSD</div>
            </div>
        </div>

        {{-- RED 2: 2 grafikona po pola širine --}}
        <div class="charts-row mb-6">
            <div class="chart-half">
                <div class="chart-title">Statusi radnih naloga</div>
                <livewire:filament.widgets.status-count-chart
                    :labels="$statusLabels"
                    :values="$statusValues"
                />
            </div>
            <div class="chart-half">
                <div class="chart-title">Prihod po tipu naloga</div>
                <livewire:filament.widgets.revenue-by-type-pie
                    :labels="$typeLabels"
                    :values="$typeValues"
                />
            </div>
        </div>

        {{-- DUGMAD ZA EXPORT NA DNU --}}
        <div class="actions-bottom">
            <x-filament::button tag="a" color="danger"
                href="{{ route('dashboard.export.pdf', ['from' => $fromCarbon->toDateString(), 'to' => $toCarbon->toDateString()]) }}"
                target="_blank">
                Export PDF
            </x-filament::button>

            <x-filament::button tag="a" color="success"
                href="{{ route('dashboard.export.excel', ['from' => $fromCarbon->toDateString(), 'to' => $toCarbon->toDateString()]) }}">
                Export Excel
            </x-filament::button>
        </div>
    </div>
</x-filament::page>