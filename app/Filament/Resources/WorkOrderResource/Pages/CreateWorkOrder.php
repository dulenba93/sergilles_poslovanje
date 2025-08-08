<?php

namespace App\Filament\Resources\WorkOrderResource\Pages;

use App\Filament\Resources\WorkOrderResource;
use App\Models\PozicijaMetraza;
use App\Models\PozicijaGarnisna;
use App\Models\PozicijaRoloZebra;
use App\Models\PozicijaPlise;
use App\Models\WorkOrder;
use App\Models\WorkOrderPosition;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateWorkOrder extends CreateRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // 1. Extract positions and remove from main payload
        $positions = $data['positions'] ?? [];
        unset($data['positions']);

        // 2. Create Work Order
        $workOrder = WorkOrder::create([
            'customer_name' => $data['customer_name'],
            'phone'         => $data['phone'],
            'email'         => $data['email'] ?? null,     // ✅ čuvanje email
            'address'       => $data['address'] ?? null,
            'note'          => $data['note'] ?? null,       // ✅ čuvanje note
            'status'        => $data['status'],
            'scheduled_at'  => $data['scheduled_at'] ?? null,
            'cena_montaze'  => $data['cena_montaze'] ?? 0,
            'total_price'   => $data['total_price'] ?? 0,
            'advance_payment' => $data['advance_payment'] ?? 0,
        ]);

       foreach ($positions as $position) {
            $type = $position['position_type'];

            if ($type === 'metraza') {
                $pozicija = PozicijaMetraza::create([
                    'duzina'      => $position['duzina'] ?? 0,
                    'visina'      => $position['visina'] ?? null,
                    'nabor'       => $position['nabor'] ?? null,
                    'broj_delova' => $position['broj_delova'] ?? null,
                    'product_id'  => $position['product_id'] ?? null,
                    'model'        => $position['model'] ?? null,     // NOVO
                    'cena'        => $position['cena'] ?? 0,
                    'name'        => $position['name'] ?? null,
                    'br_kom'     => $position['br_kom'] ?? 1,       // NOVO (mapiranje)
                ]);
            } elseif ($type === 'garnisna') {
                $pozicija = PozicijaGarnisna::create([
                    'duzina'     => $position['duzina'] ?? 0,
                    'product_id' => $position['product_id'] ?? null,
                    'cena'       => $position['cena'] ?? 0,
                    'name'       => $position['name'] ?? null,
                    'model'      => $position['model'] ?? null,       // NOVO
                    'br_kom'   => $position['br_kom'] ?? 1,         // NOVO (mapiranje)


                ]);
            } elseif ($type === 'rolo_zebra') {
                // kreiranje rolo/zebra
              // Rolo/Zebra
                    $pozicija = PozicijaRoloZebra::create([
                        'product_id'  => $position['product_id'] ?? null,
                        'name'        => $position['name'] ?? null,
                        'model'       => $position['model'] ?? null,
                        'cena'        => $position['cena'] ?? 0,
                        'sirina'      => $position['sirina'] ?? 0,
                        'visina'      => $position['visina'] ?? 0,
                        'sirina_type' => $position['sirina_type'] ?? 'mehanizam',
                        'mehanizam'   => $position['mehanizam'] ?? 'standard',
                        'broj_kom'    => $position['br_kom'] ?? 1,
                        'potez'       => $position['potez'] ?? 'levo',
                        'kacenje'     => $position['kacenje'] ?? 'plafon',
                        'maska_boja'  => $position['maska_boja'] ?? null,
                        'napomena'    => $position['napomena'] ?? null,
                    ]);
            } elseif ($type === 'plise') {
                // kreiranje plise
                            
                // Plise
                $pozicija = PozicijaPlise::create([
                    'product_id' => $position['product_id'] ?? null,
                    'name'       => $position['name'] ?? null,
                    'model'      => $position['model'] ?? null,
                    'cena'       => $position['cena'] ?? 0,
                    'sirina'     => $position['sirina'] ?? 0,
                    'visina'     => $position['visina'] ?? 0,
                    'mehanizam'  => $position['mehanizam'] ?? 'standard',
                    'broj_kom'   => $position['br_kom'] ?? 1,
                    'potez'      => $position['potez'] ?? 'levo',
                    'maska_boja' => $position['maska_boja'] ?? null,
                    'napomena'   => $position['napomena'] ?? null,
                ]);
            } else {
                continue;
            }

            WorkOrderPosition::create([
                'work_order_id' => $workOrder->id,
                'pozicija_type' => $type,
                'pozicija_id'   => $pozicija->id,
                'naziv'         => $position['name'] ?? null,
                'napomena'      => $position['napomena'] ?? null,
            ]);
        }
        return $workOrder;
    }
}
