@php
    use Carbon\Carbon;

    $fmtMoney = fn($v) => number_format((float)($v ?? 0), 2, ',', '.');
    $fmtDate  = fn($d) => $d ? Carbon::parse($d)->format('d.m.Y') : '-';
    $statusLabel = fn($s) => match($s) {
        'novi' => 'Novi', 'u_toku' => 'U toku', 'zavrsen' => 'Završen', 'otkazan' => 'Otkazan', default => ($s ?? '-')
    };
    $statusBg = fn($s) => match($s) {
        'novi' => 'background:#f59e0b;color:#000;',
        'u_toku' => 'background:#3b82f6;color:#fff;',
        'zavrsen' => 'background:#22c55e;color:#fff;',
        'otkazan' => 'background:#ef4444;color:#fff;',
        default => 'background:#9ca3af;color:#fff;',
    };
@endphp
<!doctype html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <title>Radni nalog #{{ $record->code }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 8px; }
        h2 { font-size: 14px; margin: 18px 0 8px; }
        table.grid { width:100%; border-collapse: collapse; }
        .grid th, .grid td { border:1px solid #ddd; padding:6px 8px; vertical-align: top; }
        .grid th { background:#f2f2f2; text-align:left; }
        .muted{ color:#555; }
        .mt{ margin-top:16px; }
        .small{ font-size:11px; }
        .group-title{ background:#fafafa; border:1px solid #e5e7eb; padding:6px 8px; font-weight:bold; }
        .pill{ padding:2px 6px; border-radius:4px; font-weight:bold; }
    </style>
</head>
<body>
    <h1>Radni nalog #{{ $record->code }}</h1>

    <table class="grid">
        <tr>
            <th>Ime kupca</th><td>{{ $record->customer_name }}</td>
            <th>Telefon</th><td>{{ $record->phone }}</td>
        </tr>
        <tr>
            <th>Email</th><td>{{ $record->email ?? '-' }}</td>
            <th>Tip plaćanja</th><td>{{ $record->tip_placanja }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td><span class="pill" style="{{ $statusBg($record->status) }}">{{ $statusLabel($record->status) }}</span></td>
            <th>Zakazano</th><td>{{ $fmtDate($record->scheduled_at) }}</td>
        </tr>
        <tr>
            <th>Ukupna cena</th><td>{{ $fmtMoney($record->total_price) }} RSD</td>
            <th>Avans</th><td>{{ $fmtMoney($record->advance_payment) }} RSD</td>
        </tr>
        <tr>
            <th>Preostalo</th><td>{{ $fmtMoney(($record->total_price ?? 0) - ($record->advance_payment ?? 0)) }} RSD</td>
            <th>Šifra</th><td>{{ $record->code }}</td>
        </tr>
    </table>

    @if($record->note)
        <h2>Napomena</h2>
        <div class="small">{{ $record->note }}</div>
    @endif

    @if($grouped->isNotEmpty())
        <h2>Pozicije</h2>
        @foreach($grouped as $naziv => $stavke)
            <div class="group-title">{{ $naziv }} <span class="muted small">({{ $stavke->count() }} stavki)</span></div>
            <table class="grid mt">
                <thead>
                    <tr>
                        <th style="width: 25%;">Naziv artikla</th>
                        <th style="width: 15%;">Tip</th>
                        <th style="width: 60%;">Detalji</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($stavke as $p)
                    @php
                        $tip = match($p->pozicija_type) {
                            'metraza'=>'Metraža','garnisna'=>'Garnišna','rolo_zebra'=>'Rolo/Zebra','plise'=>'Plise', default=>'Pozicija'
                        };
                        // koristimo EAGER LOADED relacije iz kontrolera
                        $pozicija = $p->metraza ?? $p->garnisna ?? $p->roloZebra ?? $p->plise ?? null;
                        $attr = $pozicija?->getAttributes() ?? [];
                    @endphp
                    @if($pozicija)
                        <tr>
                            <td>{{ $pozicija->name ?? $naziv }}</td>
                            <td>{{ $tip }}</td>
                            <td>
                                @foreach($attr as $key => $value)
                                    @continue(in_array($key, ['id','created_at','updated_at','product_id','name']))
                                    @if($value !== null && $value !== '')
                                        <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                        @if(is_numeric($value))
                                            {{ number_format($value, 2, ',', '.') }}
                                        @else
                                            {{ $value }}
                                        @endif
                                        @if(!$loop->last) | @endif
                                    @endif
                                @endforeach

                                @if($p->napomena)
                                    @if(!empty($attr)) | @endif
                                    <strong>Napomena:</strong> {{ $p->napomena }}
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        @endforeach
    @endif
</body>
</html>
