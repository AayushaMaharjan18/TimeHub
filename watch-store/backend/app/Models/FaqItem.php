<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Model;

class FaqItem extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (FaqItem $item) {
            if ($item->isDirty('answer') && $item->answer !== null) {
                $item->answer = HtmlSanitizer::clean($item->answer);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(FaqCategory::class, 'faq_category_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }
}
