<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Pregled poslovanja</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { margin: 0 0 8px 0; font-size: 22px; }
        .muted { color: #666; }
        .header { display: table; width:100%; margin-bottom: 10px; }
        .cell { display: table-cell; vertical-align: middle; }
        .right { text-align: right; }
        .logo { width: 140px; }

        .section { border:1px solid #ddd; border-radius:8px; padding:10px; margin-bottom:10px; }
        .title { font-weight:700; margin:0 0 8px 0; }

        .grid { display: table; width:100%; border-collapse: separate; border-spacing: 8px 0; }
        .col  { display: table-cell; padding:8px; border:1px solid #eee; border-radius:8px; }

        .label { font-size: 11px; color: #666; margin-bottom: 4px; }
        .val   { font-size: 18px; font-weight: 800; color:#111; }
        .pos   { color: #0a7a57; }
        .neg   { color: #b91c1c; }

        .band   { width:100%; height:10px; background:#f0f0f0; border-radius:999px; overflow:hidden; }
        .bar-in { height:100%; background:#10b981; }
        .bar-ou { height:100%; background:#ef4444; }
        .bar-ke { height:100%; background:#3b82f6; }
        .bar-fi { height:100%; background:#f59e0b; }

        .row { display: table; width:100%; border-collapse: collapse; }
        .row .cell { display: table-cell; padding:4px 0; }
        .sp   { height: 6px; }

        .pill { display:inline-block; background:#f3f4f6; border-radius:999px; padding:2px 8px; font-size:11px; color:#374151; }
    </style>
</head>
<body>
    <div class="header">
        <div class="cell"><h1>Pregled poslovanja</h1></div>
        <div class="cell right">
            @if (file_exists(public_path('storage/img/logo.png')))
                <img class="logo" src="{{ public_path('storage/img/logo.png') }}" alt="Logo">
            @endif
        </div>
    </div>
    <div class="muted" style="margin-bottom:10px">Period: <span class="pill">{{ $from->format('d.m.Y') }} – {{ $to->format('d.m.Y') }}</span></div>

    @php
        $fmt = fn($n) => number_format($n,2,',','.');
        $sumAll = max($in_total + $out_total, 0.01);
        $inPct  = min(100, round($in_total / $sumAll * 100));
        $outPct = min(100, round($out_total / $sumAll * 100));

        $inKesPct   = min(100, round(($in_kes / max($in_total,0.01))*100));
        $inFirmaPct = min(100, round(($in_firma / max($in_total,0.01))*100));
        $outKesPct  = min(100, round(($out_kes / max($out_total,0.01))*100));
        $outFirPct  = min(100, round(($out_firma / max($out_total,0.01))*100));
    @endphp

    <!-- UPLATE -->
    <div class="section">
        <div class="title">Uplate (prilivi)</div>
        <div class="grid">
            <div class="col">
                <div class="label">RN – Ukupno</div>
                <div class="val pos">{{ $fmt($wo_paid_total) }} RSD</div>
                <div class="row"><div class="cell muted">Keš</div><div class="cell right">{{ $fmt($wo_paid_kes) }} RSD</div></div>
                <div class="row"><div class="cell muted">Firma</div><div class="cell right">{{ $fmt($wo_paid_firma) }} RSD</div></div>
            </div>
            <div class="col">
                <div class="label">Prodaje – Ukupno</div>
                <div class="val pos">{{ $fmt($sale_paid_total) }} RSD</div>
                <div class="row"><div class="cell muted">Keš</div><div class="cell right">{{ $fmt($sale_paid_kes) }} RSD</div></div>
                <div class="row"><div class="cell muted">Firma</div><div class="cell right">{{ $fmt($sale_paid_firma) }} RSD</div></div>
            </div>
            <div class="col">
                <div class="label">Uplate – Zbir</div>
                <div class="val pos">{{ $fmt($in_total) }} RSD</div>
                <div class="row"><div class="cell muted">Keš</div><div class="cell right">{{ $fmt($in_kes) }} RSD</div></div>
                <div class="row"><div class="cell muted">Firma</div><div class="cell right">{{ $fmt($in_firma) }} RSD</div></div>
            </div>
        </div>

        <div class="sp"></div>
        <div class="row">
            <div class="cell" style="width:30%; font-weight:700">Uplate vs Rashodi</div>
            <div class="cell">
                <div class="band">
                    <div class="bar-in" style="width: {{ $inPct }}%"></div>
                </div>
                <div class="muted" style="font-size:11px; margin-top:4px">
                    Uplate: {{ $fmt($in_total) }} • Rashodi: {{ $fmt($out_total) }}
                </div>
            </div>
        </div>

        <div class="sp"></div>
        <div class="row">
            <div class="cell" style="width:30%; font-weight:700">Struktura uplata</div>
            <div class="cell">
                <div class="band" title="Keš ~ {{ $inKesPct }}%">
                    <div class="bar-ke" style="width: {{ $inKesPct }}%"></div>
                </div>
                <div class="muted" style="font-size:11px; margin-top:4px">
                    Keš: {{ $fmt($in_kes) }} • Firma: {{ $fmt($in_firma) }}
                </div>
            </div>
        </div>
    </div>

    <!-- RASHODI -->
    <div class="section">
        <div class="title">Troškovi i plaćanja (rashodi)</div>
        <div class="grid">
            <div class="col">
                <div class="label">Troškovi – Ukupno</div>
                <div class="val neg">-{{ $fmt($exp_total) }} RSD</div>
                <div class="row"><div class="cell muted">Keš</div><div class="cell right">-{{ $fmt($exp_kes) }} RSD</div></div>
                <div class="row"><div class="cell muted">Firma</div><div class="cell right">-{{ $fmt($exp_firma) }} RSD</div></div>
            </div>
            <div class="col">
                <div class="label">Dobavljači – Ukupno</div>
                <div class="val neg">-{{ $fmt($vp_total) }} RSD</div>
                <div class="row"><div class="cell muted">Keš</div><div class="cell right">-{{ $fmt($vp_kes) }} RSD</div></div>
                <div class="row"><div class="cell muted">Firma</div><div class="cell right">-{{ $fmt($vp_firma) }} RSD</div></div>
            </div>
            <div class="col">
                <div class="label">Rashodi – Zbir</div>
                <div class="val neg">-{{ $fmt($out_total) }} RSD</div>
                <div class="row"><div class="cell muted">Keš</div><div class="cell right">-{{ $fmt($out_kes) }} RSD</div></div>
                <div class="row"><div class="cell muted">Firma</div><div class="cell right">-{{ $fmt($out_firma) }} RSD</div></div>
            </div>
        </div>

        <div class="sp"></div>
        <div class="row">
            <div class="cell" style="width:30%; font-weight:700">Struktura rashoda</div>
            <div class="cell">
                <div class="band" title="Keš ~ {{ $outKesPct }}%">
                    <div class="bar-ke" style="width: {{ $outKesPct }}%"></div>
                </div>
                <div class="muted" style="font-size:11px; margin-top:4px">
                    Keš: -{{ $fmt($out_kes) }} • Firma: -{{ $fmt($out_firma) }}
                </div>
            </div>
        </div>
    </div>

    <!-- FINALNI PRESEK -->
    <div class="section">
        <div class="title">Finalni presek (preostalo)</div>
        <div class="grid">
            <div class="col">
                <div class="label">Preostalo ukupno</div>
                <div class="val {{ $net_total >= 0 ? 'pos' : 'neg' }}">
                    {{ $net_total >= 0 ? '' : '-' }}{{ $fmt(abs($net_total)) }} RSD
                </div>
            </div>
            <div class="col">
                <div class="label">Preostalo Keš</div>
                <div class="val {{ $net_kes >= 0 ? 'pos' : 'neg' }}">
                    {{ $net_kes >= 0 ? '' : '-' }}{{ $fmt(abs($net_kes)) }} RSD
                </div>
            </div>
            <div class="col">
                <div class="label">Preostalo Firma</div>
                <div class="val {{ $net_firma >= 0 ? 'pos' : 'neg' }}">
                    {{ $net_firma >= 0 ? '' : '-' }}{{ $fmt(abs($net_firma)) }} RSD
                </div>
            </div>
        </div>
    </div>
</body>
</html>