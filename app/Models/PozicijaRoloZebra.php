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
        'product_id',   // id proizvoda (naziv platna)
        'sirina',       // širina u metrima
        'visina',       // visina u metrima
        'sirina_type',  // da li se širina odnosi na mehanizam ili platno
        'mehanizam',    // mini ili standard
        'broj_kom',     // broj komada
        'potez',        // levo ili desno
        'kacenje',      // plafon, zid ili pvc kačenje
        'maska_boja',   // maska / boja
        'napomena',     // napomena (nullable)
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}