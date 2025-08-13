<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'product_id',
        'type',
        'unit',
        'quantity',
        'unit_price',
        'total_price',
        'paid_amount',
        'customer_description',
        'payment_type',
        'status', // DODATO
    ];

    protected static function booted(): void
    {
        static::created(function (Sale $sale) {
            if (!$sale->code) {
                $sale->update([
                    'code' => 'P-' . $sale->id . '-' . now()->format('Y'),
                ]);
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public static function unitForType(?string $type): string
    {
        return match ($type) {
            'GARNISNE', 'METRAZA' => 'm',
            'ROLO', 'ZEBRA', 'PLISE' => 'm2',
            'KOMARNICI' => 'kom',
            default => 'kol',
        };
    }
}