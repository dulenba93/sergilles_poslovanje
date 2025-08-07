<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model representing a single company expense.
 */
class MonthlyExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'type',
        'amount',
        'payment_type',
        'note',
        'month',
    ];
}