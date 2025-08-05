<?php

namespace App\Filament\Resources\WorkOrderResource\Pages;

use App\Filament\Resources\WorkOrderResource;
use App\Models\PozicijaMetraza;
use App\Models\PozicijaGarnisna;
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

        // 3. Create positions
        foreach ($positions as $position) {
            $type = $position['position_type'];

            // Create metraza or garnisna record first
            if ($type === 'metraza') {
                $pozicija = PozicijaMetraza::create([
                    'duzina'       => $position['duzina'] ?? 0,
                    'visina'       => $position['visina'] ?? null,
                    'nabor'        => $position['nabor'] ?? null,
                    'broj_delova'  => $position['broj_delova'] ?? null,
                    'product_id'   => $position['product_id'] ?? null,
                    'cena'         => $position['cena'] ?? 0,
                    'name'         => $position['name'] ?? null,
                ]);
            } elseif ($type === 'garnisna') {
                $pozicija = PozicijaGarnisna::create([
                    'duzina'     => $position['duzina'] ?? 0,
                    'product_id' => $position['product_id'] ?? null,
                    'cena'       => $position['cena'] ?? 0,
                    'name'       => $position['name'] ?? null,
                ]);
            } else {
                continue;
            }

            // Create pivot
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
