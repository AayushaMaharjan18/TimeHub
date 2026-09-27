<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quick_links' => 'array',
        'customer_service_links' => 'array',
        'payment_methods' => 'array',
        'about_values' => 'array',
        'about_stats' => 'array',
        'about_team' => 'array',
        'is_active' => 'boolean',
    ];
}
