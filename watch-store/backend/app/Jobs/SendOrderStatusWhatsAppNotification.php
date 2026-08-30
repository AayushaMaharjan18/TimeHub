<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOrderStatusWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    private const LABELS = [
        Order::STATUS_PENDING => 'received',
        Order::STATUS_CONFIRMED => 'confirmed',
        Order::STATUS_PROCESSING => 'being processed',
        Order::STATUS_PACKED => 'packed',
        Order::STATUS_SHIPPED => 'shipped',
        Order::STATUS_OUT_FOR_DELIVERY => 'out for delivery',
        Order::STATUS_DELIVERED => 'delivered',
        Order::STATUS_CANCELLED => 'cancelled',
        Order::STATUS_RETURN_REQUESTED => 'marked for return',
        Order::STATUS_RETURNED => 'returned',
        Order::STATUS_REFUNDED => 'refunded',
    ];

    public function __construct(
        public readonly int $orderId,
        public readonly string $status,
    ) {
    }

    public function handle(WhatsAppService $whatsApp): void
    {
        if (! $whatsApp->isConfigured()) {
            return;
        }

        $order = Order::find($this->orderId);
        if (! $order || ! $order->shipping_phone) {
            return;
        }

        $to = $this->toE164($order->shipping_phone);
        $label = self::LABELS[$this->status] ?? $this->status;

        $body = "Hi {$order->shipping_name}, your order #{$order->order_number} is now {$label}.";

        if ($this->status === Order::STATUS_SHIPPED && $order->tracking_number) {
            $body .= " Tracking number: {$order->tracking_number}.";
        }

        $whatsApp->sendTextMessage($to, $body);
    }

    /** Normalize a Nepal phone number to the digits-only E.164 form Meta expects. */
    private function toE164(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (strlen($digits) === 10 && str_starts_with($digits, '9')) {
            return '977' . $digits;
        }

        return $digits;
    }
}
