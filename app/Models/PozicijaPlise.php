<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model representing a single Plise position on a work order.
 *
 * A plise blind includes a fabric (product_id), measurements in
 * centimetres and configuration options similar to Rolo/Zebra.
 */
class PozicijaPlise extends Model
{
    use HasFactory;

    protected $table = 'pozicija_plise';

            protected $fillable = [
                'product_id',
                'name',   // naziv pozicije
                'model',  // ručni naziv modela
                'cena',   // cena
                'sirina',
                'visina',
                'mehanizam',
                'broj_kom',
                'potez',
                'maska_boja',
                'napomena',
            ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}