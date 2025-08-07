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
        'product_id',  // naziv platna (product_id)
        'sirina',      // širina u centimetrima
        'visina',      // visina u centimetrima
        'mehanizam',   // mini ili standard
        'broj_kom',    // broj komada
        'potez',       // levo ili desno
        'maska_boja',  // boja/opis maske
        'napomena',    // napomena (može biti null)
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}