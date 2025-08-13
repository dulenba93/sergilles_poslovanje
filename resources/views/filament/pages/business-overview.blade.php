<x-filament::page>
    <style>
        .mb-4 { margin-bottom: 12px; }
        .mb-6 { margin-bottom: 18px; }
        .row-scroll {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            padding-bottom: 6px;
        }
        .card {
            flex: 0 0 auto;
            min-width: 220px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px;
            background: #fff;
        }
        .label { font-size: 12px; color: #6b7280; margin-bottom: 6px; white-space: nowrap; }
        .value { font-size: 22px; font-weight: 700; color: #111827; white-space: nowrap; }
        .muted { color: #6b7280; }
        .neg { color: #b91c1c; }
        .pos { color: #065f46; }
        .section-title { font-weight: 600; margin: 6px 0 10px 0; }
        .hr { border-top: 1px solid #e5e7eb; margin: 14px 0; }
        .actions { display: flex; gap: 8px; }
    </style>

    {{-- Filter (od-do) --}}
    <form wire:submit.prevent="submitFiltersForm" class="mb-4">
        {{ $this->filtersForm }}
    </form>

    @php
        $o = $this->getOverview();
        $fmt = fn($n) => number_format($n, 2, ',', '.');
        $from = $o['range']['from']->format('d.m.Y');
        $to   = $o['range']['to']->format('d.m.Y');
    @endphp

    <div class="muted mb-6">Period: {{ $from }} – {{ $to }}</div>

    {{-- UPLATE – SVE U JEDNOM REDU --}}
    <div class="section-title">Uplate (prilivi)</div>
    <div class="row-scroll mb-6">
        {{-- Radni nalozi --}}
        <div class="card"><div class="label">RN – Ukupno (plaćeno)</div><div class="value pos">{{ $fmt($o['wo_paid_total']) }} RSD</div></div>
        <div class="card"><div class="label">RN – Keš</div><div class="value pos">{{ $fmt($o['wo_paid_kes']) }} RSD</div></div>
        <div class="card"><div class="label">RN – Firma</div><div class="value pos">{{ $fmt($o['wo_paid_firma']) }} RSD</div></div>
   </div>
      <div class="row-scroll mb-6">
        {{-- Prodaje --}}
        <div class="card"><div class="label">Prodaje – Ukupno (plaćeno)</div><div class="value pos">{{ $fmt($o['sale_paid_total']) }} RSD</div></div>
        <div class="card"><div class="label">Prodaje – Keš</div><div class="value pos">{{ $fmt($o['sale_paid_kes']) }} RSD</div></div>
        <div class="card"><div class="label">Prodaje – Firma</div><div class="value pos">{{ $fmt($o['sale_paid_firma']) }} RSD</div></div>
   </div>
      <div class="row-scroll mb-6">

        {{-- Zajedno --}}
        <div class="card"><div class="label">Uplate – Ukupno</div><div class="value pos">{{ $fmt($o['in_total']) }} RSD</div></div>
        <div class="card"><div class="label">Uplate – Keš</div><div class="value pos">{{ $fmt($o['in_kes']) }} RSD</div></div>
        <div class="card"><div class="label">Uplate – Firma</div><div class="value pos">{{ $fmt($o['in_firma']) }} RSD</div></div>
    </div>


    {{-- TROŠKOVI – SVE U JEDNOM REDU --}}
    <div class="section-title">Troškovi i plaćanja (rashodi)</div>
    <div class="row-scroll mb-6">
        {{-- Troškovi --}}
        <div class="card"><div class="label">Troškovi – Ukupno</div><div class="value neg">-{{ $fmt($o['exp_total']) }} RSD</div></div>
        <div class="card"><div class="label">Troškovi – Keš</div><div class="value neg">-{{ $fmt($o['exp_kes']) }} RSD</div></div>
        <div class="card"><div class="label">Troškovi – Firma</div><div class="value neg">-{{ $fmt($o['exp_firma']) }} RSD</div></div>
   </div>
      <div class="row-scroll mb-6">
        {{-- Dobavljači --}}
        <div class="card"><div class="label">Dobavljači – Ukupno</div><div class="value neg">-{{ $fmt($o['vp_total']) }} RSD</div></div>
        <div class="card"><div class="label">Dobavljači – Keš</div><div class="value neg">-{{ $fmt($o['vp_kes']) }} RSD</div></div>
        <div class="card"><div class="label">Dobavljači – Firma</div><div class="value neg">-{{ $fmt($o['vp_firma']) }} RSD</div></div>
   </div>
      <div class="row-scroll mb-6">
        {{-- Zajedno rashodi --}}
        <div class="card"><div class="label">Rashodi – Ukupno</div><div class="value neg">-{{ $fmt($o['out_total']) }} RSD</div></div>
        <div class="card"><div class="label">Rashodi – Keš</div><div class="value neg">-{{ $fmt($o['out_kes']) }} RSD</div></div>
        <div class="card"><div class="label">Rashodi – Firma</div><div class="value neg">-{{ $fmt($o['out_firma']) }} RSD</div></div>
    </div>

    <div class="hr"></div>

    {{-- Finalni presek --}}
    <div class="section-title">Finalni presek (preostalo)</div>
    <div class="row-scroll mb-6">
        <div class="card">
            <div class="label">Preostalo ukupno</div>
            <div class="value {{ $o['net_total'] >= 0 ? 'pos' : 'neg' }}">
                {{ $o['net_total'] >= 0 ? '' : '-' }}{{ $fmt(abs($o['net_total'])) }} RSD
            </div>
        </div>
        <div class="card">
            <div class="label">Preostalo Keš</div>
            <div class="value {{ $o['net_kes'] >= 0 ? 'pos' : 'neg' }}">
                {{ $o['net_kes'] >= 0 ? '' : '-' }}{{ $fmt(abs($o['net_kes'])) }} RSD
            </div>
        </div>
        <div class="card">
            <div class="label">Preostalo Firma</div>
            <div class="value {{ $o['net_firma'] >= 0 ? 'pos' : 'neg' }}">
                {{ $o['net_firma'] >= 0 ? '' : '-' }}{{ $fmt(abs($o['net_firma'])) }} RSD
            </div>
        </div>
    </div>

    {{-- Export dugmad na dnu --}}
    @php
        $fromQ = $o['range']['from']->toDateString();
        $toQ   = $o['range']['to']->toDateString();
    @endphp
    <div class="actions">
        <x-filament::button tag="a" color="danger"
            href="{{ route('business.overview.export.pdf', ['from' => $fromQ, 'to' => $toQ]) }}"
            target="_blank">
            Export PDF
        </x-filament::button>

        <x-filament::button tag="a" color="success"
            href="{{ route('business.overview.export.excel', ['from' => $fromQ, 'to' => $toQ]) }}">
            Export Excel
        </x-filament::button>
    </div>
</x-filament::page>