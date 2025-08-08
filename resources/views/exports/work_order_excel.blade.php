@php
    use Carbon\Carbon;
    $fmtMoney = fn($v) => number_format((float)($v ?? 0), 2, ',', '.');
    $fmtDate  = fn($d) => $d ? Carbon::parse($d)->format('d.m.Y') : '-';
    $statusLabel = fn($s) => match($s) {
        'novi'=>'Novi','u_toku'=>'U toku','zavrsen'=>'Završen','otkazan'=>'Otkazan', default => ($s ?? '-')
    };
@endphp

<table>
    <tr><td colspan="4"><strong>Radni nalog #{{ $record->code }}</strong></td></tr>
    <tr>
        <td>Ime kupca</td><td>{{ $record->customer_name }}</td>
        <td>Telefon</td><td>{{ $record->phone }}</td>
    </tr>
    <tr>
        <td>Email</td><td>{{ $record->email ?? '-' }}</td>
        <td>Tip plaćanja</td><td>{{ $record->tip_placanja }}</td>
    </tr>
    <tr>
        <td>Status</td><td>{{ $statusLabel($record->status) }}</td>
        <td>Zakazano</td><td>{{ $fmtDate($record->scheduled_at) }}</td>
    </tr>
    <tr>
        <td>Ukupna cena</td><td>{{ $fmtMoney($record->total_price) }}</td>
        <td>Avans</td><td>{{ $fmtMoney($record->advance_payment) }}</td>
    </tr>
    <tr>
        <td>Preostalo</td><td>{{ $fmtMoney(($record->total_price ?? 0) - ($record->advance_payment ?? 0)) }}</td>
        <td>Šifra</td><td>{{ $record->code }}</td>
    </tr>
</table>

@if($record->note)
    <table><tr><td>&nbsp;</td></tr></table>
    <table>
        <tr><td><strong>Napomena</strong></td></tr>
        <tr><td>{{ $record->note }}</td></tr>
    </table>
@endif

@if($grouped->isNotEmpty())
    <table><tr><td>&nbsp;</td></tr></table>
    <table>
        <tr><td><strong>Pozicije</strong></td></tr>
    </table>

    @foreach($grouped as $naziv => $stavke)
        <table>
            <tr><td><strong>{{ $naziv }}</strong></td><td>({{ $stavke->count() }} stavki)</td></tr>
        </table>
        <table border="1">
            <thead>
                <tr>
                    <th>Naziv artikla</th>
                    <th>Tip</th>
                    <th>Detalji</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stavke as $p)
                    @php
                        $tip = match($p->pozicija_type) {
                            'metraza'=>'Metraža','garnisna'=>'Garnišna','rolo_zebra'=>'Rolo/Zebra','plise'=>'Plise', default=>'Pozicija'
                        };
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
                                        {{ ucfirst(str_replace('_', ' ', $key)) }}:
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
                                    Napomena: {{ $p->napomena }}
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        <table><tr><td>&nbsp;</td></tr></table>
    @endforeach
@endif
