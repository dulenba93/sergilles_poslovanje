<?php

namespace App\Filament\Resources\WorkOrderResource\Pages;

use App\Filament\Resources\WorkOrderResource;
use App\Models\PozicijaGarnisna;
use App\Models\PozicijaMetraza;
use App\Models\Product;
use App\Models\WorkOrder;
use App\Models\WorkOrderPosition;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditWorkOrder extends EditRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove positions so they don't interfere with main model
        unset($data['positions']);
        return $data;
    }

protected function handleRecordUpdate(Model $record, array $data): Model
{
    // Update the work order itself
    $record->update($data);

    $positions = request()->input('data.positions', []);

    foreach ($positions as $positionData) {
        $type = $positionData['position_type'] ?? null;
        $pozicijaId = $positionData['pozicija_id'] ?? null;

        if (! $type) {
            continue;
        }

        // --- 1. Upsert PozicijaMetraza ili PozicijaGarnisna ---
        if ($type === 'metraza') {
            $pozicija = $pozicijaId
                ? \App\Models\PozicijaMetraza::find($pozicijaId)
                : new \App\Models\PozicijaMetraza();

            $pozicija->fill([
                'duzina' => $positionData['duzina'] ?? 0,
                'visina' => $positionData['visina'] ?? null,
                'nabor' => $positionData['nabor'] ?? null,
                'broj_delova' => $positionData['broj_delova'] ?? null,
                'product_id' => $positionData['product_id'] ?? null,
                'cena' => $positionData['cena'] ?? 0,
                'name' => $positionData['name'] ?? null,
            ]);
            $pozicija->save();
        } elseif ($type === 'garnisna') {
            $pozicija = $pozicijaId
                ? \App\Models\PozicijaGarnisna::find($pozicijaId)
                : new \App\Models\PozicijaGarnisna();

            $pozicija->fill([
                'duzina' => $positionData['duzina'] ?? 0,
                'product_id' => $positionData['product_id'] ?? null,
                'cena' => $positionData['cena'] ?? 0,
                'name' => $positionData['name'] ?? null,
            ]);
            $pozicija->save();
        } else {
            continue; // skip unsupported types
        }

        // --- 2. Veza sa WorkOrderPosition pivot tabelom ---
        WorkOrderPosition::updateOrCreate(
            [
                'work_order_id' => $record->id,
                'pozicija_type' => $type,
                'pozicija_id' => $pozicija->id,
            ],
            [
                'naziv' => $positionData['name'] ?? null,
                'napomena' => $positionData['napomena'] ?? null,
            ]
        );
    }

    return $record;
}



    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Add existing positions to form state
        $positions = WorkOrderPosition::where('work_order_id', $data['id'])->get();
        $formPositions = [];

        foreach ($positions as $position) {
            if ($position->pozicija_type === 'metraza') {
                $pozicija = PozicijaMetraza::find($position->pozicija_id);
            } elseif ($position->pozicija_type === 'garnisna') {
                $pozicija = PozicijaGarnisna::find($position->pozicija_id);
            } else {
                continue;
            }

            if (! $pozicija) {
                continue;
            }

            $formPositions[] = array_merge(
                $pozicija->toArray(),
                [
                    'pozicija_id' => $pozicija->id,
                    'position_type' => $position->pozicija_type,
                    'name' => $pozicija->name ?? null,
                    'napomena' => $position->napomena ?? null,
                ]
            );
        }

        $data['positions'] = $formPositions;
        return $data;
    }
}
