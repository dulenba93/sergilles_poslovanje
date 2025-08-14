@php
    use App\Models\PozicijaMetraza;
    use App\Models\PozicijaGarnisna;
    use App\Models\PozicijaRoloZebra;
    use App\Models\PozicijaPlise;

    /** ---------------------------------------------------------------
     * Inputs from controller
     * ---------------------------------------------------------------*/
    // Expecting these to be passed by controller:
    // - $accountNumber (string)
    // - $pdvIncluded (bool) -> true means input prices are GROSS (with VAT)
    $accountNumber = $accountNumber ?? '265-6240310000065-53';
    $pdvIncluded   = (bool)($pdvIncluded ?? false);

    /** ---------------------------------------------------------------
     * Company data (account number now dynamic)
     * ---------------------------------------------------------------*/
    $company = [
        'name'         => 'Ser Gilles',
        'address'      => 'Prve Šumadijske Brigade 9/2, Rakovica, Beograd',
        'email'        => 'office@zavesesergilles.rs',
        'phone'        => '+381 65 90 11 798',
        'mbr'          => '67886828',
        'pib'          => '114831508',
        'sif_del'      => '4753',
        'racun'        => $accountNumber, // <-- dynamic account number from controller
        'mesto'        => 'Beograd, 11550',
        'predracun_br' => null,
        'poziv_na_broj'=> null,
        'za'           => $workOrder->customer_name,
    ];

    /** ---------------------------------------------------------------
     * Logo
     * ---------------------------------------------------------------*/
    $logoPath = public_path('storage/img/logo.png');
    $hasLogo  = file_exists($logoPath);

    /** ---------------------------------------------------------------
     * Items preparation with PDV toggle
     * If $pdvIncluded = true  => DB prices are GROSS; convert to NET by /1.2
     * If $pdvIncluded = false => DB prices are already NET; keep as is
     * ---------------------------------------------------------------*/
    $items    = [];
    $totalBez = 0.0;

    // $grouped is provided by controller (grouped positions by name)
    $grouped = $grouped ?? collect();

    if ($grouped->isNotEmpty()) {
        foreach ($grouped as $naziv => $stavke) {
            foreach ($stavke as $poz) {
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

                $productId   = $product->id;
                $productCode = $product->code ?? '';
                $productName = $product->name ?? '';
                $vendorName  = $product->vendor->name ?? '';

                // ---------- Quantity & UOM per type ----------
                $jm = 'm';
                $quantity = 0.0;

                switch ($poz->pozicija_type) {
                    case 'garnisna':
                        $width    = (float)($model->sirina ?? $model->duzina ?? 0);
                        $kom      = (float)($model->broj_kom ?? 1);
                        $quantity = $width * $kom;
                        $jm = 'm';
                        break;

                    case 'metraza':
                        $length   = (float)($model->duzina ?? $model->sirina ?? 0);
                        $kom      = (float)($model->broj_kom ?? 1);
                        $quantity = $length * $kom;
                        $jm = 'm';
                        break;

                    case 'plise':
                    case 'rolo_zebra':
                        $height       = (float)($model->visina ?? 0);
                        $width        = (float)($model->sirina ?? $model->duzina ?? 0);
                        $kom          = (float)($model->broj_kom ?? 1);
                        $areaPerPiece = $height * $width;
                        if ($areaPerPiece < 1) $areaPerPiece = 1; // min 1 m2 per piece
                        $quantity = $areaPerPiece * $kom;
                        $jm = 'm2';
                        break;

                    default:
                        $quantity = 1.0;
                        $jm = 'm';
                }

                // ---------- Price handling with PDV toggle ----------
                $rawUnit = (float)($model->cena ?? 0.0); // price from DB

                // Convert to NET if incoming prices include VAT
                $unitPriceNet = $pdvIncluded ? ($rawUnit / 1.2) : $rawUnit;

                $linePriceNet = $unitPriceNet * $quantity; // NET line total
                $linePdv      = $linePriceNet * 0.20;      // VAT amount

                // Initialize aggregated bucket per product
                if (! isset($items[$productId])) {
                    $items[$productId] = [
                        'rb'               => 0,
                        'dobavljac'        => $vendorName,
                        'naziv'            => trim(($productCode ? $productCode . ' - ' : '') . $productName),
                        'jm'               => $jm,
                        'kol'              => 0.0,
                        'cena'             => $unitPriceNet,     // NET unit price (will look ~20% less if PDV included)
                        'popust'           => 0,
                        'pdv_proc'         => 20,
                        'cena_sa_popustom' => 0.0,               // we keep NET sums here
                        'pdv'              => 0.0,               // VAT amount
                        'iznos'            => 0.0,               // NET amount (line total)
                    ];
                } else {
                    // fill vendor if empty
                    if (empty($items[$productId]['dobavljac']) && $vendorName) {
                        $items[$productId]['dobavljac'] = $vendorName;
                    }
                    // prefer m2 if UOMs are mixed
                    if ($items[$productId]['jm'] !== $jm && in_array('m2', [$items[$productId]['jm'], $jm])) {
                        $items[$productId]['jm'] = 'm2';
                    }
                }

                // Accumulate
                $items[$productId]['kol']              += $quantity;
                $items[$productId]['cena_sa_popustom'] += $linePriceNet; // NET
                $items[$productId]['pdv']              += $linePdv;
                $items[$productId]['iznos']            += $linePriceNet; // NET

                $totalBez += $linePriceNet; // NET base for totals
            }
        }
    }

    // Re-index items (RB)
    $indexedItems = [];
    $rbCounter    = 1;
    foreach ($items as $id => $item) {
        $item['rb'] = $rbCounter++;
        $indexedItems[] = $item;
    }
    $items = $indexedItems;

    // Montage service (apply same PDV logic)
    $montageRaw = (float) ($workOrder->cena_montaze ?? 0);
    if ($montageRaw > 0) {
        $montageNet = $pdvIncluded ? ($montageRaw / 1.2) : $montageRaw;
        $items[] = [
            'rb'               => count($items) + 1,
            'dobavljac'        => '',
            'naziv'            => 'Usluga montaže',
            'jm'               => 'kom',
            'kol'              => 1,
            'cena'             => $montageNet,             // NET unit
            'popust'           => 0,
            'pdv_proc'         => 20,
            'cena_sa_popustom' => $montageNet,             // NET
            'pdv'              => $montageNet * 0.2,
            'iznos'            => $montageNet,             // NET
        ];
        $totalBez += $montageNet; // NET
    }

    // Totals (NET base + VAT)
    $pdvTotal      = $totalBez * 0.2;       // VAT
    $totalZaUplatu = $totalBez + $pdvTotal; // GROSS

    // Formatting helper
    function rsd($v) { return number_format((float)$v, 2, ',', '.'); }
@endphp
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <title>Profaktura #{{ $workOrder->code }}</title>
    <style>
        /* ====== PAGE / TYPO ====== */
        @page { margin: 18mm 14mm; } /* compact margins for A4 */
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1   { font-size: 20px; margin: 0 0 6px; }
        .muted { color: #6b7280; }
        .small { font-size: 11px; }
        .mb-4 { margin-bottom: 4px; } .mb-6 { margin-bottom: 6px; } .mb-8 { margin-bottom: 8px; } .mb-12 { margin-bottom: 12px; }

        .grid {
            display: grid;
            grid-template-columns: auto auto; /* content-sized columns */
            gap: 10px;
            justify-content: end; /* align cards to the right */
        }
        .card { border:1px solid #e5e7eb; border-radius:8px; padding:8px 10px; }
        .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }
        .pill { border:1px solid #e5e7eb; border-radius:999px; padding:2px 8px; font-size:11px; }
        .right { text-align: right; } .center { text-align: center; }

        /* ====== HEADER ====== */
        .header { display:flex; align-items:center; justify-content: space-between; margin-bottom: 10px; }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: flex-end; /* push to right */
        }
        .brand img { height: 48px; }
        .brand .title { font-weight: 800; font-size: 18px; letter-spacing: .2px; }
        .brand .subtitle { font-weight: 700; font-size: 18px; }
        .company { text-align: right; line-height: 1.4; }

        /* ====== TABLES ====== */
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #e5e7eb; padding:6px 6px; vertical-align: top; }
        th { background:#f3f4f6; font-weight:700; }
        .tight td { padding: 5px 6px; }
        .no-border td, .no-border th { border: none; }
        .summary th { text-align: right; background: #fafafa; }
        .summary td { text-align: right; }

        /* ====== WIDTH HELPERS ====== */
        .w-20 { width:20px } .w-40 { width:40px } .w-50 { width: 50px } .w-60 { width:60px } .w-70 { width:70px }
        .w-80 { width:80px } .w-90 { width:90px } .w-110 { width:110px } .w-120 { width:120px }
        .break-inside-avoid { page-break-inside: avoid; }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="header">
        <div class="brand">
            @if($hasLogo)
                <img src="{{ $logoPath }}" alt="Logo">
            @else
                <div class="title">Ser Gilles Zavese</div>
            @endif
            </br></br>
            <div class="subtitle">PROFAKTURA #{{ $workOrder->code }}</div>
        </div>
        <div class="company small">
            <div>{{ $company['address'] }}</div>
            <div>Email: {{ $company['email'] }} • Tel: {{ $company['phone'] }}</div>
            <div>Mbr: {{ $company['mbr'] }} &nbsp; Pib: {{ $company['pib'] }} &nbsp; Šifra delatnosti: {{ $company['sif_del'] }}</div>
            <div>Mesto izdavanja: {{ $company['mesto'] }}</div>
            <div>Tekući račun: {{ $company['racun'] }}</div>
        </div>
    </div>

    <!-- INFO BLOCKS -->
    <div class="grid mb-12">
        <div class="card">
            <div class="row">
                <div><span class="pill">Datum ponude</span></div>
                <div class="right">{{ now()->format('d.m.Y') }}</div>
            </div>
            @if($company['predracun_br'] || $company['poziv_na_broj'])
                <div class="row" style="margin-top:6px">
                    @if($company['predracun_br'])
                        <div>Predračun br: <strong>{{ $company['predracun_br'] }}</strong></div>
                    @endif
                    @if($company['poziv_na_broj'])
                        <div class="right">Poziv na broj: <strong>{{ $company['poziv_na_broj'] }}</strong></div>
                    @endif
                </div>
            @endif
        </div>
        <div class="card">
            <div class="row">
                <div>Kontakt</div>
                <div class="right"><strong>{{ $workOrder->phone }}</strong></div>
            </div>
            <div class="row">
                <div>Email</div>
                <div class="right">{{ $workOrder->email ?: '—' }}</div>
            </div>
            <div class="row">
                <div>Adresa</div>
                <div class="right">{{ $workOrder->address ?: '—' }}</div>
            </div>
        </div>
    </div>
    </br></br>

    <!-- ITEMS -->
    <div class="break-inside-avoid">
        <table class="tight">
            <thead>
            <tr>
                <th class="center w-20">RB</th>
                <th class="left w-110">Šifra - Naziv</th>
                <th class="center w-20">JM</th>
                <th class="right w-20">Kol</th>
                <th class="right w-80">Cena</th>
                <th class="center w-20">P%</th>
                <th class="center w-20">PDV%</th>
                <th class="right w-90">Cena sa popustom</th>
                <th class="right w-20">PDV</th>
                <th class="right w-90">Iznos</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($items as $it)
                <tr>
                    <td class="center">{{ $it['rb'] }}</td>
                    <td>{{ $it['naziv'] }}</td>
                    <td class="center">{{ $it['jm'] }}</td>
                    <td class="right">{{ rsd($it['kol']) }}</td>
                    <td class="right">{{ rsd($it['cena']) }}</td>
                    <td class="center">{{ (int) $it['popust'] }}</td>
                    <td class="center">{{ (int) $it['pdv_proc'] }}</td>
                    <td class="right">{{ rsd($it['cena_sa_popustom']) }}</td>
                    <td class="right">{{ rsd($it['pdv']) }}</td>
                    <td class="right">{{ rsd($it['iznos']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="center muted">Nema stavki za prikaz.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    </br></br>

    <!-- SUMMARY -->
    <div class="break-inside-avoid" style="margin-top:10px">
        <table class="summary">
            <tbody>
            <tr>
                <th colspan="10">Ukupno:</th>
                <td class="right">{{ rsd($totalBez) }}</td>
            </tr>
            <tr>
                <th colspan="10">Popust:</th>
                <td class="right">{{ rsd(0) }}</td>
            </tr>
            <tr>
                <th colspan="10">Iznos bez PDV-a:</th>
                <td class="right">{{ rsd($totalBez) }}</td>
            </tr>
            <tr>
                <th colspan="10">PDV:</th>
                <td class="right">{{ rsd($pdvTotal) }}</td>
            </tr>
            <tr>
                <th colspan="10">ZA UPLATU:</th>
                <td class="right"><strong>{{ rsd($totalZaUplatu) }}</strong></td>
            </tr>
            </tbody>
        </table>
    </br>
    </br>
        <div class="small muted" style="margin-top:6px">
            Napomena: profaktura je informativnog karaktera. Rok važenja ponude 15 dana, osim ako nije drugačije naznačeno.
        </div>
    </div>
</body>
</html>
