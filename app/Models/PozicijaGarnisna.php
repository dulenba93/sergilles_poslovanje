<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PozicijaGarnisna extends Model
{
    use HasFactory;

    // Ako ne koristiš konvencionalni naziv tabele ("pozicija_garnisnas"), moraš ga ručno navesti:
    protected $table = 'pozicija_garnisna';

    protected $fillable = [
        'product_id',
        'duzina',
        'cena',
        'name', // ako si dodavao ovo polje prilikom kreiranja pozicije u CreateWorkOrder
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
