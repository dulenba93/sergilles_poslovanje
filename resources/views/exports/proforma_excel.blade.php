@php
    /**
     * Build a flat list of invoice items from the grouped positions.
     * - Aggregate by product_id (one row per product)
     * - Quantity rules:
     *      garnisna, metraza -> (sirina or duzina) * broj_kom, JM = m
     *      plise, rolo_zebra -> max(1, visina * sirina) per piece * broj_kom, JM = m2
     */

    use App\Models\PozicijaMetraza;
    use App\Models\PozicijaGarnisna;
    use App\Models\PozicijaRoloZebra;
    use App\Models\PozicijaPlise;

    $items    = [];
    $totalBez = 0;

    if (isset($grouped) && $grouped->isNotEmpty()) {
        foreach ($grouped as $naziv => $stavke) {
            foreach ($stavke as $poz) {
                // STRIKTNO odredi model na osnovu type + id:
                $model = match ($poz->pozicija_type) {
                    'metraza'    => PozicijaMetraza::find($poz->pozicija_id),
                    'garnisna'   => PozicijaGarnisna::find($poz->pozicija_id),
                    'rolo_zebra' => PozicijaRoloZebra::find($poz->pozicija_id),
                    'plise'      => PozicijaPlise::find($poz->pozicija_id),
                    default      => null,
                };
                if (! $model) continue;

                $product = $model->product ?? null;
                if (! $product) continue;

                $productId    = $product->id;
                $productCode  = $product->code ?? '';
                $productName  = $product->name ?? '';
                $vendorName   = $product->vendor->name ?? '';  // <<— DODATO

                // Količina i JM:
                $jm = 'm';
                $quantity = 0;

                switch ($poz->pozicija_type) {
                    case 'garnisna':
                        $width = (float)($model->sirina ?? $model->duzina ?? 0);
                        $kom   = (float)($model->broj_kom ?? 1);
                        $quantity = $width * $kom;
                        $jm = 'm';
                        break;

                    case 'metraza':
                        $length = (float)($model->duzina ?? $model->sirina ?? 0);
                        $kom    = (float)($model->broj_kom ?? 1);
                        $quantity = $length * $kom;
                        $jm = 'm';
                        break;

                    case 'plise':
                    case 'rolo_zebra':
                        $height       = (float)($model->visina ?? 0);
                        $width        = (float)($model->sirina ?? $model->duzina ?? 0);
                        $kom          = (float)($model->broj_kom ?? 1);
                        $areaPerPiece = $height * $width;
                        if ($areaPerPiece < 1) $areaPerPiece = 1;
                        $quantity = $areaPerPiece * $kom;
                        $jm = 'm2';
                        break;

                    default:
                        $quantity = 1; $jm = 'm';
                }

                $unitPrice = (float)($model->cena ?? 0);
                $linePrice = $unitPrice * $quantity;

                if (! isset($items[$productId])) {
                    $items[$productId] = [
                        'rb'       => 0, // assigned later
                        'dobavljac'=> $vendorName,                                               // <<— DODATO
                        'naziv'    => trim(($productCode ? $productCode . ' - ' : '') . $productName),
                        'jm'       => $jm,
                        'kol'      => 0,
                        'cena'     => $unitPrice,
                        'popust'   => 0,
                        'pdv_proc' => 20,
                        'cena_sa_popustom' => 0,
                        'pdv'      => 0,
                        'iznos'    => 0,
                    ];
                } else {
                    // ako je vendor prazan u postojećoj stavci, popuni ga
                    if (empty($items[$productId]['dobavljac']) && $vendorName) {
                        $items[$productId]['dobavljac'] = $vendorName;
                    }
                }

                // ako neki deo koristi m2, preferiraj m2
                if ($items[$productId]['jm'] !== $jm && in_array('m2', [$items[$productId]['jm'], $jm])) {
                    $items[$productId]['jm'] = 'm2';
                }

                $items[$productId]['kol']              += $quantity;
                $items[$productId]['cena_sa_popustom'] += $linePrice;
                $items[$productId]['pdv']              += $linePrice * 0.2;
                $items[$productId]['iznos']            += $linePrice;

                $totalBez += $linePrice;
            }
        }
    }

    // reindeksiranje i RB
    $indexedItems = [];
    $rbCounter    = 1;
    foreach ($items as $id => $item) {
        $item['rb'] = $rbCounter++;
        $indexedItems[] = $item;
    }
    $items = $indexedItems;

    // montaža kao posebna stavka
    $montagePrice = (float) ($workOrder->cena_montaze ?? 0);
    if ($montagePrice > 0) {
        $items[] = [
            'rb'    => count($items) + 1,
            'dobavljac' => '',
            'naziv' => 'Usluga montaže',
            'jm'    => 'kom',
            'kol'   => 1,
            'cena'  => $montagePrice,
            'popust' => 0,
            'pdv_proc' => 20,
            'cena_sa_popustom' => $montagePrice,
            'pdv'   => $montagePrice * 0.2,
            'iznos' => $montagePrice,
        ];
        $totalBez += $montagePrice;
    }

    $pdvTotal      = $totalBez * 0.2;
    $totalZaUplatu = $totalBez + $pdvTotal;
@endphp

<table>
    <thead>
        <tr>
            <th>RB</th>
            <th>Dobavljač</th>   {{-- <<— NOVA KOLONA --}}
            <th>Šifra - Naziv</th>
            <th>JM</th>
            <th>Kol</th>
            <th>Cena</th>
            <th>Popust%</th>
            <th>Pdv%</th>
            <th>Cena sa popustom</th>
            <th>PDV</th>
            <th>Iznos</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $item)
            <tr>
                <td>{{ $item['rb'] }}</td>
                <td>{{ $item['dobavljac'] }}</td> {{-- <<— NOVO --}}
                <td>{{ $item['naziv'] }}</td>
                <td>{{ $item['jm'] }}</td>
                <td>{{ number_format($item['kol'], 2, ',', '.') }}</td>
                <td>{{ number_format($item['cena'], 2, ',', '.') }}</td>
                <td>{{ $item['popust'] }}</td>
                <td>{{ $item['pdv_proc'] }}</td>
                <td>{{ number_format($item['cena_sa_popustom'], 2, ',', '.') }}</td>
                <td>{{ number_format($item['pdv'], 2, ',', '.') }}</td>
                <td>{{ number_format($item['iznos'], 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table>
    <tr>
        <th colspan="10" style="text-align: right">Ukupno:</th> {{-- ranije 9 --}}
        <td>{{ number_format($totalBez, 2, ',', '.') }}</td>
    </tr>
    <tr>
        <th colspan="10" style="text-align: right">Popust:</th>
        <td>{{ number_format(0, 2, ',', '.') }}</td>
    </tr>
    <tr>
        <th colspan="10" style="text-align: right">Iznos bez PDV-a:</th>
        <td>{{ number_format($totalBez, 2, ',', '.') }}</td>
    </tr>
    <tr>
        <th colspan="10" style="text-align: right">PDV:</th>
        <td>{{ number_format($pdvTotal, 2, ',', '.') }}</td>
    </tr>
    <tr>
        <th colspan="10" style="text-align: right">ZA UPLATU:</th>
        <td>{{ number_format($totalZaUplatu, 2, ',', '.') }}</td>
    </tr>
</table>
