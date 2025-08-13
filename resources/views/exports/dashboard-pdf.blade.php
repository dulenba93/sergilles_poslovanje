<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .h { font-weight: bold; margin-top: 8px; }
        .grid { width: 100%; }
        .cell { display: inline-block; width: 19%; vertical-align: top; margin-right: 1%; border:1px solid #ddd; padding:8px; border-radius:4px; }
        .mt { margin-top: 12px; }
        .mb { margin-bottom: 8px; }
    </style>
</head>
<body>
    <div style="text-align:right">
        <img src="{{ public_path('storage/img/logo.png') }}" alt="Logo" style="width:140px">
    </div>

    <h2>Dashboard ({{ $from->format('d.m.Y') }} – {{ $to->format('d.m.Y') }})</h2>

    @php $fmt = fn($n)=>number_format($n,2,',','.'); @endphp

    <div class="grid mt">
        <div class="cell"><div class="h">Porudžbine</div>{{ $data['totalOrders'] }}</div>
        <div class="cell"><div class="h">Očekivano</div>{{ $fmt($data['expected']) }} RSD</div>
        <div class="cell"><div class="h">Naplaćeno</div>{{ $fmt($data['paid']) }} RSD</div>
        <div class="cell"><div class="h">Preostalo</div>{{ $fmt($data['remaining']) }} RSD</div>
        <div class="cell"><div class="h">Montaže</div>{{ $fmt($data['montaze']) }} RSD</div>
    </div>

    <div class="mt">
        <div class="h mb">Statusi</div>
        <ul>
            @php
                $labels = ['new'=>'Novi','in_progress'=>'U toku','done'=>'Završen','cancelled'=>'Otkazan'];
            @endphp
            @foreach($labels as $k=>$label)
                <li>{{ $label }}: {{ $data['statusCounts'][$k] ?? 0 }}</li>
            @endforeach
        </ul>
    </div>

    <div class="mt">
        <div class="h mb">Prihod po tipu</div>
        <ul>
            @foreach($data['typeShare'] as $type => $sum)
                <li>{{ $type }}: {{ $fmt($sum) }} RSD</li>
            @endforeach
        </ul>
    </div>
</body>
</html>