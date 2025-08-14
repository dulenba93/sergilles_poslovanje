<?php

namespace App\Exports;

use App\Models\WorkOrder;
use App\Models\WorkOrderPosition;
use App\Models\PozicijaMetraza;
use App\Models\PozicijaGarnisna;
use App\Models\PozicijaRoloZebra;
use App\Models\PozicijaPlise;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SelectedPositionsExport implements WithMultipleSheets
{
    /** @var array<int> */
    protected array $workOrderIds;

    public function __construct(array $workOrderIds)
    {
        $this->workOrderIds = $workOrderIds;
    }

    public function sheets(): array
    {
        // id => code mapa
        $ordersMap = WorkOrder::query()
            ->whereIn('id', $this->workOrderIds)
            ->pluck('code', 'id');

        // pivot pozicije za selektovane naloge
        $pivot = WorkOrderPosition::query()
            ->whereIn('work_order_id', $this->workOrderIds)
            ->get(['work_order_id', 'pozicija_type', 'pozicija_id', 'naziv']);

        $byType = $pivot->groupBy('pozicija_type');

        $sheets = [];

        $makeSheet = function (string $type, Collection $items) use ($ordersMap) {
            if ($items->isEmpty()) {
                return null;
            }

            $ids = $items->pluck('pozicija_id')->all();

            switch ($type) {
                case 'metraza':
                    $models = PozicijaMetraza::with('product')->whereIn('id', $ids)->get()->keyBy('id');
                    $title  = 'METRAŽA';
                    // DUŽINA (m), VISINA (cm)
                    $labelOverrides = [
                        'name'            => 'Naziv',
                        'product_name'    => 'Proizvod',
                        'model'           => 'Model',
                        'broj_kom'        => 'Broj Komada',
                        'duzina'          => 'Dužina (m)',
                        'visina'          => 'Visina (cm)',
                        'work_order_code' => 'Šifra Naloga',
                    ];
                    break;

                case 'garnisna':
                    $models = PozicijaGarnisna::with('product')->whereIn('id', $ids)->get()->keyBy('id');
                    $title  = 'GARNIŠNE';
                    // DUŽINA (m)
                    $labelOverrides = [
                        'name'            => 'Naziv',
                        'product_name'    => 'Proizvod',
                        'model'           => 'Model',
                        'broj_kom'        => 'Broj Komada',
                        'duzina'          => 'Dužina (m)',
                        'work_order_code' => 'Šifra Naloga',
                    ];
                    break;

                case 'rolo_zebra':
                    $models = PozicijaRoloZebra::with('product')->whereIn('id', $ids)->get()->keyBy('id');
                    $title  = 'ROLO/ZEBRA';
                    // ŠIRINA (m), VISINA (m)
                    $labelOverrides = [
                        'name'            => 'Naziv',
                        'product_name'    => 'Proizvod',
                        'model'           => 'Model',
                        'broj_kom'        => 'Broj Komada',
                        'sirina'          => 'Širina (m)',
                        'visina'          => 'Visina (m)',
                        'work_order_code' => 'Šifra Naloga',
                    ];
                    break;

                case 'plise':
                    $models = PozicijaPlise::with('product')->whereIn('id', $ids)->get()->keyBy('id');
                    $title  = 'PLISE';
                    // ŠIRINA (cm), VISINA (cm)
                    $labelOverrides = [
                        'name'            => 'Naziv',
                        'product_name'    => 'Proizvod',
                        'model'           => 'Model',
                        'broj_kom'        => 'Broj Komada',
                        'sirina'          => 'Širina (cm)',
                        'visina'          => 'Visina (cm)',
                        'work_order_code' => 'Šifra Naloga',
                    ];
                    break;

                default:
                    return null;
            }

            // Polja koja izbacujemo univerzalno
            $skip = ['id', 'product_id', 'created_at', 'updated_at', 'cena', 'pozicija_naziv'];

            // Početni prioritetni poredak kolona
            $priority = ['name', 'product_name', 'model', 'broj_kom', 'duzina'];

            $rows    = [];
            $allKeys = [];

            foreach ($items as $p) {
                $m = $models->get($p->pozicija_id);
                if (!$m) continue;

                $attrs = $m->getAttributes();

                // naziv proizvoda
                if (method_exists($m, 'product') && $m->relationLoaded('product') && $m->product) {
                    $attrs['product_name'] = $m->product->name ?? null;
                }

                // čisti polja koja ne izvozimo
                foreach ($skip as $k) unset($attrs[$k]);

                // referenca na nalog (samo code)
                $attrs['work_order_code'] = $ordersMap[$p->work_order_id] ?? $p->work_order_id;

                $rows[]   = $attrs;
                $allKeys  = array_unique(array_merge($allKeys, array_keys($attrs)));
            }

            if (empty($rows)) {
                return null;
            }

            // trailing uvek poslednje
            $trailing = ['work_order_code'];

            // srednje kolone = sve osim priority i trailing
            $middle = array_values(array_filter(
                $allKeys,
                fn($k) => !in_array($k, $trailing, true) && !in_array($k, $priority, true)
            ));

            // konačni raspored (ubaci samo postojeće iz priority)
            $orderedKeys = array_values(array_unique([
                ...array_filter($priority, fn($k) => in_array($k, $allKeys, true)),
                ...$middle,
                ...$trailing,
            ]));

            // normalizuj redove po rasporedu
            $normalized = array_map(function ($r) use ($orderedKeys) {
                $out = [];
                foreach ($orderedKeys as $k) $out[$k] = $r[$k] ?? null;
                return $out;
            }, $rows);

            return new \App\Exports\Support\PositionTypeSheet(
                $title,
                $orderedKeys,
                $normalized,
                $labelOverrides // → specifične oznake jedinica po tipu
            );
        };

        foreach (['metraza','garnisna','rolo_zebra','plise'] as $type) {
            $sheet = $makeSheet($type, $byType->get($type, collect()));
            if ($sheet) $sheets[] = $sheet;
        }

        if (empty($sheets)) {
            $sheets[] = new \App\Exports\Support\PositionTypeSheet(
                'PRAZNO',
                ['info'],
                [['info' => 'Nema pozicija za izvezene naloge.']],
                []
            );
        }

        return $sheets;
    }
}
