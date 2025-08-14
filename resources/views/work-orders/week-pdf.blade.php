@php
    $srDays = ['Ponedeljak','Utorak','Sreda','Četvrtak','Petak','Subota','Nedelja'];
    function money_rs($v) { return number_format((float)$v, 2, ',', '.'); }
@endphp
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <title>Nedeljni pregled montaža</title>
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .h1 { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
        .muted { color: #555; }
        .mb-8 { margin-bottom: 8px; }
        .mb-12 { margin-bottom: 12px; }
        .day-block { page-break-inside: avoid; margin-bottom: 14px; }
        .day-head { font-weight: 700; padding: 6px 8px; background: #f3f4f6; border: 1px solid #e5e7eb; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 6px; vertical-align: top; }
        th { background: #fafafa; text-align: left; }
        .right { text-align: right; }
        .nowrap { white-space: nowrap; }
        .small { font-size: 11px; color: #555; }
    </style>
</head>
<body>
    <div class="h1">Nedeljni pregled montaža</div>
    <div class="mb-12 muted">
        Period: {{ $weekStart->format('d.m.Y') }} — {{ $weekEnd->format('d.m.Y') }}
    </div>

    @foreach ($days as $index => $day)
        @php
            $key = $day->toDateString();
            $list = $grouped->get($key, collect());
        @endphp

        <div class="day-block">
            <div class="day-head">
                {{ $srDays[$index] }} — {{ $day->format('d.m.Y') }}
                <span class="small">({{ $list->count() }} zakazano)</span>
            </div>

            @if ($list->isEmpty())
                <div class="small" style="padding:8px 0">Nema zakazanih montaža.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th class="nowrap">Šifra</th>
                            <th>Klijent</th>
                            <th>Kontakt</th>
                            <th>Adresa</th>
                            <th class="nowrap">Tip</th>
                            <th>Status</th>
                            <th class="right nowrap">Preostalo (RSD)</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($list as $o)
                        @php
                            $due = (float)($o->total_price ?? 0) - (float)($o->advance_payment ?? 0);
                        @endphp
                        <tr>
                            <td class="nowrap">#{{ $o->code }}</td>
                            <td>{{ $o->customer_name }}</td>
                            <td>
                                @if($o->phone)
                                    {{ $o->phone }}
                                @endif
                                @if($o->email)
                                    <div class="small">{{ $o->email }}</div>
                                @endif
                            </td>
                            <td>{{ $o->address ?: '—' }}</td>
                            <td class="nowrap">{{ $o->type ?: '—' }}</td>
                            <td class="nowrap">{{ ucfirst(str_replace('_',' ',$o->status ?? '—')) }}</td>
                            <td class="right nowrap">{{ money_rs($due) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach
</body>
</html>
