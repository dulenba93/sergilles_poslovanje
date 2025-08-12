<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Radni nalog #{{ $workOrder->code }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h2 { margin: 0 0 10px 0; }
        h3 { margin: 16px 0 8px 0; }
        h4 { margin: 10px 0 6px 0; }
        .row { width: 100%; }
        .col { display: inline-block; vertical-align: top; width: 32.5%; }
        .mb-6 { margin-bottom: 6px; }
        .mb-8 { margin-bottom: 8px; }
        .mb-12 { margin-bottom: 12px; }
        .mb-16 { margin-bottom: 16px; }
        .box { border: 1px solid #ddd; padding: 8px; border-radius: 4px; }
        .muted { color: #444; }
        .hr { border-top: 1px solid #ddd; margin: 12px 0; }
        .label { font-weight: bold; }
        .pill { display: inline-block; padding: 2px 6px; border-radius: 4px; color: #fff; font-size: 11px; }
        .status-new { background: #f59e0b; }
        .status-inprog { background: #3b82f6; }
        .status-done { background: #22c55e; }
        .status-cancelled { background: #ef4444; }
        .logo { position: absolute; top: 5px; right: 60px; width: 180px; }
        .one-line { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .kv { margin-right: 10px; }
    </style>
</head>
<body>
    {{-- Logo --}}
    <img src="{{ public_path('storage/img/logo.png') }}" class="logo" alt="Logo">

    <h2>Radni nalog #{{ $workOrder->code }}</h2>
    <br><br>
    <div class="hr"></div>
    <br>

    {{-- Opšte informacije (3 u redu) --}}
    <div class="row mb-12">
        <div class="col mb-8"><span class="label">Ime kupca:</span> {{ $workOrder->customer_name }}</div>
        <div class="col mb-8"><span class="label">Telefon:</span> {{ $workOrder->phone }}</div>
        <div class="col mb-8"><span class="label">Email:</span> {{ $workOrder->email ?? '-' }}</div>

        <div class="col mb-8"><span class="label">Tip plaćanja:</span> {{ $workOrder->tip_placanja }}</div>
        <div class="col mb-8">
            <span class="label">Status:</span>
            @php
                $statusClass = match ($workOrder->status) {
                    'new'        => 'status-new',
                    'in_progress'=> 'status-inprog',
                    'done'       => 'status-done',
                    'cancelled'  => 'status-cancelled',
                    default      => 'status-new',
                };
                $statusText = [
                    'new' => 'Novi',
                    'in_progress' => 'U toku',
                    'done' => 'Završen',
                    'cancelled' => 'Otkazan',
                ][$workOrder->status] ?? $workOrder->status;
            @endphp
            <span class="pill {{ $statusClass }}">{{ $statusText }}</span>
        </div>
        <div class="col mb-8">
            <span class="label">Zakazano za:</span>
            {{ $workOrder->scheduled_at ? \Carbon\Carbon::parse($workOrder->scheduled_at)->format('d.m.Y') : '-' }}
        </div>

        <div class="col mb-8"><span class="label">Ukupna cena:</span> {{ number_format($workOrder->total_price, 2, ',', '.') }} RSD</div>
        <div class="col mb-8"><span class="label">Avans:</span> {{ number_format($workOrder->advance_payment, 2, ',', '.') }} RSD</div>
        <div class="col mb-8">
            <span class="label">Preostalo:</span>
            {{ number_format(($workOrder->total_price - $workOrder->advance_payment), 2, ',', '.') }} RSD
        </div>

        <div class="col mb-8">
            <span class="label">Cena montaže:</span>
            {{ number_format(($workOrder->cena_montaze), 2, ',', '.') }} RSD
        </div>
    </div>

    @if(filled($workOrder->note))
        <div class="mb-12">
            <span class="label">Napomena:</span><br>
            <span class="muted">{{ $workOrder->note }}</span>
        </div>
    @endif

    <div class="hr"></div>

    {{-- Pozicije grupisane po pivot nazivu --}}
    <h3>Pozicije</h3>

    @php
        // Dozvoljena polja po tipu (redosled prikaza)
        $fieldsByType = [
            'metraza'    => ['model','duzina','visina','nabor','broj_delova','broj_kom'],
            'garnisna'   => ['model','duzina','broj_delova','broj_kom'],
            'rolo_zebra' => ['model','sirina','visina','sirina_type','mehanizam','broj_kom','potez','kacenje','maska_boja'],
            'plise'      => ['model','sirina','visina','mehanizam','broj_kom','potez','maska_boja'],
        ];

        // Labele za polja
        $labels = [
            'model'        => 'Model',
            'duzina'       => 'Dužina',
            'visina'       => 'Visina',
            'sirina'       => 'Širina',
            'nabor'        => 'Nabor',
            'broj_delova'  => 'Broj delova',
            'broj_kom'     => 'Br kom',
            'sirina_type'  => 'Širina tip',
            'mehanizam'    => 'Mehanizam',
            'potez'        => 'Potez',
            'kacenje'      => 'Kačenje',
            'maska_boja'   => 'Maska boja',
        ];

        $formatNum = function ($v) {
            if ($v === null || $v === '') return null;
            if (is_numeric($v)) {
                return (floor($v) == $v)
                    ? number_format($v, 0, ',', '.')
                    : number_format($v, 2, ',', '.');
            }
            return $v;
        };
    @endphp

    @foreach($groupedPositions as $groupName => $items)
        <h4>{{ $groupName ?: 'Bez naziva' }} <span class="muted">({{ $items->count() }} stavki)</span></h4>

        @foreach($items as $pivot)
            @php
                // STROGO po tipu (bez „??“)
                $model = match ($pivot->pozicija_type) {
                    'metraza'    => $pivot->metraza,
                    'garnisna'   => $pivot->garnisna,
                    'rolo_zebra' => $pivot->roloZebra,
                    'plise'      => $pivot->plise,
                    default      => null,
                };

                $typeLabel = match ($pivot->pozicija_type) {
                    'metraza'    => 'Metraža',
                    'garnisna'   => 'Garnišna',
                    'rolo_zebra' => 'Rolo/Zebra',
                    'plise'      => 'Plise',
                    default      => strtoupper($pivot->pozicija_type),
                };

                $allowed = $fieldsByType[$pivot->pozicija_type] ?? [];
                $product = $model?->product; // <— PROIZVOD
                $productLabel = '';
                if ($product) {
                    $code = $product->code ?? '';
                    $name = $product->name ?? '';
                    $productLabel = trim($code ? ($code . ' - ' . $name) : $name);
                }
            @endphp

            @if($model)
                <div class="box mb-12">
                    <div class="mb-8">
                        <span class="label">{{ $typeLabel }}:</span>
                        {{ $model->name ?? ($groupName ?: 'Bez naziva') }}
                    </div>

                    {{-- Proizvod + samo polja relevantna za dati tip --}}
                    @php
                        $parts = [];

                        // 1) Proizvod (ako postoji)
                        if ($productLabel !== '') {
                            $parts[] = ' ' . $productLabel;
                        }

                        // 2) Ostala polja po whitelist-u
                        foreach ($allowed as $key) {
                            $val = $model->{$key} ?? null;
                            $val = $formatNum($val);
                            if ($val === null || $val === '') continue;

                            $label = $labels[$key] ?? ucfirst(str_replace('_',' ', $key));
                            $parts[] = "{$label}: {$val}";
                        }

                        // 3) Napomena sa pivota (ako postoji)
                        if (filled($pivot->napomena)) {
                            $parts[] = 'Napomena pozicije: ' . $pivot->napomena;
                        }
                    @endphp

                    <div class="one-line">{{ implode(' | ', $parts) }}</div>
                </div>
            @endif
        @endforeach
    @endforeach
</body>
</html>
