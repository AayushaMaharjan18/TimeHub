<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /** Published posts whose publish date has arrived. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }
}
