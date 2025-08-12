<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkOrderPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'pozicija_type',
        'pozicija_id',
        'naziv',
        'napomena',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    // VAŽNO: bez where() uslova — te kolone nema u child tabelama
    public function metraza()
    {
        return $this->belongsTo(PozicijaMetraza::class, 'pozicija_id');
    }

    public function garnisna()
    {
        return $this->belongsTo(PozicijaGarnisna::class, 'pozicija_id');
    }

    public function roloZebra()
    {
        return $this->belongsTo(PozicijaRoloZebra::class, 'pozicija_id');
    }

    public function plise()
    {
        return $this->belongsTo(PozicijaPlise::class, 'pozicija_id');
    }

    // Jedinstvena “pozicija” za view/pdf
    public function getPozicijaAttribute()
    {
        return match ($this->pozicija_type) {
            'metraza'    => $this->metraza,
            'garnisna'   => $this->garnisna,
            'rolo_zebra' => $this->roloZebra,
            'plise'      => $this->plise,
            default      => null,
        };
    }
}
