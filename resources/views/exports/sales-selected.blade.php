<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h2 { margin-bottom: 8px; }
        h3 { margin: 14px 0 6px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .muted { color: #666; }
    </style>
</head>
<body>
    <div style="text-align:right">
        <img src="{{ public_path('storage/img/logo.png') }}" alt="Logo" style="width:140px">
    </div>

    <h2>Izvod selektovanih prodaja</h2>

    @foreach($grouped as $name => $items)
        <h3>{{ $name }}</h3>
        <table>
            <thead>
                <tr>
                    <th>Šifra</th>
                    <th>Tip</th>
                    <th>JM</th>
                    <th>Količina</th>
                    <th>Cena/JM</th>
                    <th>Ukupno</th>
                    <th>Plaćeno</th>
                    <th>Plaćanje</th>
                    <th>Kupac/Opis</th>
                    <th>Datum</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $s)
                    <tr>
                        <td>{{ $s->code }}</td>
                        <td>{{ $s->type }}</td>
                        <td>{{ $s->unit }}</td>
                        <td>{{ number_format($s->quantity, 2, ',', '.') }}</td>
                        <td>{{ number_format($s->unit_price ?? 0, 2, ',', '.') }} RSD</td>
                        <td>{{ number_format($s->total_price, 2, ',', '.') }} RSD</td>
                        <td>{{ number_format($s->paid_amount, 2, ',', '.') }} RSD</td>
                        <td>{{ $s->payment_type }}</td>
                        <td>{{ $s->customer_description }}</td>
                        <td>{{ optional($s->created_at)->format('d.m.Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>
</html>