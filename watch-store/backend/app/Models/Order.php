<?php

namespace App\Models;

use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Jobs\SendOrderStatusWhatsAppNotification;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_PACKED = 'packed';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_RETURN_REQUESTED = 'return_requested';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_REFUNDED = 'refunded';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_PROCESSING,
        self::STATUS_PACKED,
        self::STATUS_SHIPPED,
        self::STATUS_OUT_FOR_DELIVERY,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
        self::STATUS_RETURN_REQUESTED,
        self::STATUS_RETURNED,
        self::STATUS_REFUNDED,
    ];

    /**
     * Once an order reaches one of these it must not silently move to a
     * different status (a cancelled order becoming "delivered" is a
     * data-integrity bug, not a legitimate flow). Enforced in the
     * `updating` event below so it holds no matter which code path
     * changes the status — API, admin panel, tinker, anything.
     */
    public const TERMINAL_STATUSES = [
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
        self::STATUS_RETURNED,
        self::STATUS_REFUNDED,
    ];

    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_UNPAID = 'unpaid';
    public const PAYMENT_STATUS_INITIATED = 'initiated';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_FAILED = 'failed';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';

    protected $guarded = [];

    protected $casts = [
        'estimated_delivery_date' => 'date',
    ];

    /**
     * Optional note attached to the next auto-logged status-history row.
     * Not persisted as a column — set it right before save() when you want
     * the history entry to carry a message (e.g. "Payment confirmed via eSewa.").
     */
    public ?string $statusChangeNote = null;

    protected static function booted(): void
    {
        static::updating(function (Order $order) {
            if (! $order->isDirty('status')) {
                return;
            }

            $original = $order->getOriginal('status');

            if (
                in_array($original, self::TERMINAL_STATUSES, true)
                && $original !== $order->status
            ) {
                throw new InvalidOrderStatusTransitionException(
                    "Order #{$order->order_number} is already '{$original}' and cannot be moved to '{$order->status}'."
                );
            }
        });

        static::created(function (Order $order) {
            $order->statusHistories()->create([
                'status' => $order->status,
                'note' => $order->statusChangeNote ?? 'Order placed.',
            ]);
        });

        static::updated(function (Order $order) {
            if (! $order->wasChanged('status')) {
                return;
            }

            $order->statusHistories()->create([
                'status' => $order->status,
                'note' => $order->statusChangeNote,
            ]);

            if (config('whatsapp.notify_on_status_change')) {
                SendOrderStatusWhatsAppNotification::dispatch($order->id, $order->status);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Convenience helper for programmatic status changes (payment providers,
     * admin actions) that want to attach a note. Returns false instead of
     * throwing when the status name is invalid; a terminal-state violation
     * still throws InvalidOrderStatusTransitionException via the model event.
     */
    public function recordStatusChange(string $newStatus, ?string $note = null): bool
    {
        if (! in_array($newStatus, self::STATUSES, true)) {
            return false;
        }

        $this->statusChangeNote = $note;
        $this->status = $newStatus;
        $this->save();

        return true;
    }
}
