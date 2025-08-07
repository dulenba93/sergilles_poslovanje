<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PozicijaMetraza extends Model
{
    use HasFactory;

    protected $table = 'pozicija_metraza';

    protected $fillable = [
        'product_id',
        'duzina',
        'visina',
        'nabor',
        'broj_delova',
        'cena',
        'name',
        'br_kom',
        'model',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
