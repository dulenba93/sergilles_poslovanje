<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model representing a single company expense.
 */
class Expense extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',   // optional description
        'type',          // Serbian expense type (e.g. Šivenje)
        'amount',        // numeric amount
        'payment_type',  // KES or FIRMA
        'note',          // optional note
        'month',         // month string (YYYY-MM)
    ];
}