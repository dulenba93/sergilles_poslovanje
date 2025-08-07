<?php

namespace App\Filament\Resources\WorkOrderResource\Pages;

use App\Filament\Resources\WorkOrderResource;
use App\Models\PozicijaGarnisna;
use App\Models\PozicijaMetraza;
use App\Models\PozicijaPlise;
use App\Models\PozicijaRoloZebra;
use App\Models\WorkOrderPosition;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditWorkOrder extends EditRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $formPositions = [];

        $positions = WorkOrderPosition::where('work_order_id', $data['id'])->get();

        foreach ($positions as $position) {
            $pozicija = null;

            if ($position->pozicija_type === 'metraza') {
                $pozicija = PozicijaMetraza::find($position->pozicija_id);
            } elseif ($position->pozicija_type === 'garnisna') {
                $pozicija = PozicijaGarnisna::find($position->pozicija_id);
            } elseif ($position->pozicija_type === 'rolo_zebra') {
                $pozicija = PozicijaRoloZebra::find($position->pozicija_id);
            } elseif ($position->pozicija_type === 'plise') {
                $pozicija = PozicijaPlise::find($position->pozicija_id);
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
                    'model' => $pozicija->model ?? null,
                    'cena' => $pozicija->cena ?? null,
                    'napomena' => $position->napomena ?? null,
                ]
            );
        }

        $data['positions'] = $formPositions;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        WorkOrderPosition::where('work_order_id', $record->id)->delete();

        $positions = $data['positions'] ?? [];

        foreach ($positions as $positionData) {
            $type = $positionData['position_type'] ?? null;
            $pozicijaId = $positionData['pozicija_id'] ?? null;

            if (! $type) {
                continue;
            }

            if (! $pozicijaId) {
                if ($type === 'metraza') {
                    $pozicija = PozicijaMetraza::create([
                        'duzina' => $positionData['duzina'] ?? 0,
                        'visina' => $positionData['visina'] ?? null,
                        'nabor' => $positionData['nabor'] ?? null,
                        'broj_delova' => $positionData['broj_delova'] ?? null,
                        'product_id' => $positionData['product_id'] ?? null,
                        'cena' => $positionData['cena'] ?? 0,
                        'name' => $positionData['name'] ?? null,
                    ]);
                } elseif ($type === 'garnisna') {
                    $pozicija = PozicijaGarnisna::create([
                        'duzina' => $positionData['duzina'] ?? 0,
                        'product_id' => $positionData['product_id'] ?? null,
                        'cena' => $positionData['cena'] ?? 0,
                        'name' => $positionData['name'] ?? null,
                    ]);
                } elseif ($type === 'rolo_zebra') {
                    $pozicija = PozicijaRoloZebra::create([
                        'product_id' => $positionData['product_id'] ?? null,
                        'name' => $positionData['name'] ?? null,
                        'model' => $positionData['model'] ?? null,
                        'cena' => $positionData['cena'] ?? 0,
                        'sirina' => $positionData['sirina'] ?? 0,
                        'visina' => $positionData['visina'] ?? 0,
                        'sirina_type' => $positionData['sirina_type'] ?? 'mehanizam',
                        'mehanizam' => $positionData['mehanizam'] ?? 'standard',
                        'broj_kom' => $positionData['br_kom'] ?? 1,
                        'potez' => $positionData['potez'] ?? 'levo',
                        'kacenje' => $positionData['kacenje'] ?? 'plafon',
                        'maska_boja' => $positionData['maska_boja'] ?? null,
                        'napomena' => $positionData['napomena'] ?? null,
                    ]);
                } elseif ($type === 'plise') {
                    $pozicija = PozicijaPlise::create([
                        'product_id' => $positionData['product_id'] ?? null,
                        'name' => $positionData['name'] ?? null,
                        'model' => $positionData['model'] ?? null,
                        'cena' => $positionData['cena'] ?? 0,
                        'sirina' => $positionData['sirina'] ?? 0,
                        'visina' => $positionData['visina'] ?? 0,
                        'mehanizam' => $positionData['mehanizam'] ?? 'standard',
                        'broj_kom' => $positionData['br_kom'] ?? 1,
                        'potez' => $positionData['potez'] ?? 'levo',
                        'maska_boja' => $positionData['maska_boja'] ?? null,
                        'napomena' => $positionData['napomena'] ?? null,
                    ]);
                } else {
                    continue;
                }

                WorkOrderPosition::create([
                    'work_order_id' => $record->id,
                    'pozicija_type' => $type,
                    'pozicija_id' => $pozicija->id,
                    'naziv' => $positionData['name'] ?? null,
                    'napomena' => $positionData['napomena'] ?? null,
                ]);

                continue;
            }

            if ($type === 'metraza') {
                $pozicija = PozicijaMetraza::find($pozicijaId);
            } elseif ($type === 'garnisna') {
                $pozicija = PozicijaGarnisna::find($pozicijaId);
            } elseif ($type === 'rolo_zebra') {
                $pozicija = PozicijaRoloZebra::find($pozicijaId);
            } elseif ($type === 'plise') {
                $pozicija = PozicijaPlise::find($pozicijaId);
            }

            if (! $pozicija) {
                continue;
            }

            $pozicija->update(
                collect($positionData)->except(['pozicija_id', 'position_type', 'name', 'napomena'])->toArray()
            );

            WorkOrderPosition::updateOrCreate(
                [
                    'work_order_id' => $record->id,
                    'pozicija_type' => $type,
                    'pozicija_id' => $pozicijaId,
                ],
                [
                    'naziv' => $positionData['name'] ?? null,
                    'napomena' => $positionData['napomena'] ?? null,
                ]
            );
        }

        return $record;
    }
}
