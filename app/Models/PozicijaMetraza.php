<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model representing a metraža (fabric or material by length) position on a work order.
 *
 * This version of the model has been updated to unify the quantity column to
 * `broj_kom`. The legacy `br_kom` field has been removed from the schema,
 * and this model no longer provides any fallback. All quantity values
 * should now be written to and read from `broj_kom`.
 */
class PozicijaMetraza extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pozicija_metraza';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'duzina',
        'visina',
        'nabor',
        'broj_delova',
        'cena',
        'name',
        'model',
        // Use `broj_kom` instead of `br_kom` for number of pieces
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