<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;

class WorkOrderViewController extends Controller
{
    public function show(WorkOrder $workOrder)
    {
        // Eager-load ako expand-row koristi pozicije/proizvode:
        $workOrder->load([
            'positions',               // WorkOrder -> hasMany WorkOrderPosition
            'positions.metraza',
            'positions.garnisna',
            'positions.roloZebra',
            'positions.plise',
        ]);

        // Render TAČAN view:
        // resources/views/filament/resources/work-order-resource/partials/expand-row.blade.php
        return view('filament.resources.work-order-resource.partials.expand-row', [
            // Daj oba imena da bi postojećI partial radio bez izmene
            'record'     => $workOrder,
            'workOrder'  => $workOrder,
        ]);
    }
}
