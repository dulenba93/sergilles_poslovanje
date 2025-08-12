<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model representing a garnisna (curtain rod or similar) position on a work order.
 *
 * This model has been updated to unify the quantity column to `broj_kom`.
 * The legacy `br_kom` column has been dropped from the database and is no
 * longer referenced here; there is no fallback logic. All quantity values
 * should be stored and accessed via `broj_kom`.
 */
class PozicijaGarnisna extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pozicija_garnisna';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'duzina',
        'cena',
        'name',
        'model',
        // Use unified column for quantity
        'broj_kom',
    ];


    /**
     * Relationship to the associated product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}