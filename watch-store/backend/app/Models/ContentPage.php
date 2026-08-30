<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Model;

class ContentPage extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (ContentPage $page) {
            if ($page->isDirty('content') && $page->content !== null) {
                $page->content = HtmlSanitizer::clean($page->content);
            }

            if ($page->isDirty('status') && $page->status === self::STATUS_PUBLISHED && ! $page->published_at) {
                $page->published_at = now();
            }

            if (auth('web')->check()) {
                $page->updated_by = auth('web')->id();
            }
        });
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }
}
