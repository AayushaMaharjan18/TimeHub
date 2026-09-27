<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingDistrict extends Model
{
    protected $guarded = [];

    protected $casts = [
        'cost' => 'float',
        'is_active' => 'boolean',
    ];
}
