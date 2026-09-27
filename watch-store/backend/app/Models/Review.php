<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    // Explicit allow-list rather than $guarded = []: a stray key in a save
    // payload (e.g. a Filament form field bound to a relation name) is
    // silently dropped instead of Eloquent trying to write it as a real
    // column and failing with a SQL error — see ReviewAdminEditTest.
    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'title',
        'comment',
        'status',
        'is_approved',
        'is_visible_on_homepage',
        'is_verified_purchase',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
        'is_visible_on_homepage' => 'boolean',
        'is_verified_purchase' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Keep the legacy boolean in sync with the moderation status.
        static::saving(function (Review $review): void {
            if ($review->isDirty('status') || $review->exists === false) {
                $review->is_approved = $review->status === self::STATUS_APPROVED;
            }
        });

        // Recalculate the parent product's average rating / count whenever a
        // review is created, updated, or deleted.
        static::saved(function (Review $review): void {
            $review->product?->recalculateRating();
        });

        static::deleted(function (Review $review): void {
            $review->product?->recalculateRating();
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

