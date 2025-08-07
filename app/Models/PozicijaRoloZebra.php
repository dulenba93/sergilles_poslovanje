<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model representing a single Rolo/Zebra position on a work order.
 *
 * Each record holds the measurements and configuration for a Rolo or
 * Zebra blind.  The `product_id` links to the selected fabric or
 * component in the products table.
 */
class PozicijaRoloZebra extends Model
{
    use HasFactory;

    // Custom table name (Laravel would otherwise pluralize incorrectly)
    protected $table = 'pozicija_rolo_zebra';

    protected $fillable = [
        'product_id',        // naziv platna (product_id)
        'sirina_mehanizma',  // širina mehanizma u metrima
        'visina_platna',     // visina platna u metrima
        'sirina_platna',     // širina platna u metrima (može biti null)
        'mehanizam',         // mini ili standard
        'broj_kom',          // broj komada
        'potez',             // levo ili desno
        'kacenje',           // plafon, zid ili pvc_kacenje
        'maska_boja',        // opis maske/boje
        'napomena',          // napomena (može biti null)
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}