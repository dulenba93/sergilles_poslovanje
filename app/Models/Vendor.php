<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'vendor_type',
        'contact',
        'website',
        'contact_info',
        'notes',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(VendorPayment::class);
    }
}