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

        public function metraza()
        {
            return $this->belongsTo(\App\Models\PozicijaMetraza::class, 'pozicija_id')->where('pozicija_type', 'metraza');
        }
        public function garnisna()
        {
            return $this->belongsTo(\App\Models\PozicijaGarnisna::class, 'pozicija_id')->where('pozicija_type', 'garnisna');
        }
        public function roloZebra()
        {
            return $this->belongsTo(\App\Models\PozicijaRoloZebra::class, 'pozicija_id')
                ->where('pozicija_type', 'rolo_zebra');
        }

        public function plise()
        {
            return $this->belongsTo(\App\Models\PozicijaPlise::class, 'pozicija_id')
                ->where('pozicija_type', 'plise');
        }
}
