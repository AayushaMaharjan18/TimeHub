<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaqCategory extends Model
{
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(FaqItem::class)->orderBy('sort_order');
    }
}
