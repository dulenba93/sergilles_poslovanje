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
            'product_id',
            'name',   // naziv pozicije
            'model',  // ručni naziv modela
            'cena',   // cena
            'sirina',
            'visina',
            'sirina_type',
            'mehanizam',
            'broj_kom',
            'potez',
            'kacenje',
            'maska_boja',
            'napomena',
        ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}