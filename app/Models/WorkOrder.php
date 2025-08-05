<?php

// App\Models\WorkOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'phone',
        'email',
        'address',
        'status',
        'scheduled_at',
        'total_price',
        'advance_payment',
        'cena_montaze',
        'note',
        'order_type',
        'code',
        'tip_placanja',
    ];

    public function positions(): HasMany
    {
    return $this->hasMany(WorkOrderPosition::class);
    }

    protected static function booted()
    {
        static::created(function (WorkOrder $order) {
               // Generiši CODE u formatu ID-GODINA, npr. 15-2025
            $order->update([
                'code' => $order->id . '-' . now()->format('Y'),
            ]);


            if (request()->has('positions')) {
                foreach (request()->get('positions') as $positionData) {
                    $type = $positionData['position_type'];

                    if ($type === 'metraza') {
                        $pozicija = PozicijaMetraza::create([
                            'duzina' => $positionData['duzina'] ?? 0,
                            'visina' => $positionData['visina'] ?? null,
                            'nabor' => $positionData['nabor'] ?? null,
                            'broj_delova' => $positionData['broj_delova'] ?? null,
                            'product_id' => $positionData['product_id'] ?? null,
                            'cena' => $positionData['cena'] ?? 0,
                        ]);
                    } elseif ($type === 'garnisna') {
                        $pozicija = PozicijaGarnisna::create([
                            'duzina' => $positionData['duzina'] ?? 0,
                            'product_id' => $positionData['product_id'] ?? null,
                            'cena' => $positionData['cena'] ?? 0,
                        ]);
                    } else {
                        continue;
                    }

                    WorkOrderPosition::create([
                        'work_order_id' => $order->id,
                        'pozicija_type' => $type,
                        'pozicija_id' => $pozicija->id,
                        'naziv' => $positionData['name'] ?? null,
                        'napomena' => $positionData['napomena'] ?? null,
                    ]);
                }
            }
        });
    }
}
