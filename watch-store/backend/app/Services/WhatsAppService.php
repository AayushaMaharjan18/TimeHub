<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the Meta WhatsApp Cloud API. Credentials always come
 * from config()/env() — never hardcoded, never logged. When the integration
 * is not configured, send() is a documented no-op (returns false) rather
 * than throwing, so the rest of the app (order status updates, etc.) keeps
 * working without a WhatsApp Business account attached.
 */
class WhatsAppService
{
    public function isConfigured(): bool
    {
        return filled(config('whatsapp.access_token')) && filled(config('whatsapp.phone_number_id'));
    }

    /**
     * Send a free-form text message. Meta only allows free-form messages
     * within a 24h customer-service window; outside that window a template
     * message is required. This method is intentionally limited to text
     * messages for order-status notices sent shortly after customer contact.
     */
    public function sendTextMessage(string $toE164, string $body): bool
    {
        if (! $this->isConfigured()) {
            Log::info('WhatsApp not configured; skipping message send.', ['to' => $this->mask($toE164)]);
            return false;
        }

        $url = sprintf(
            '%s/%s/%s/messages',
            rtrim(config('whatsapp.base_url'), '/'),
            config('whatsapp.api_version'),
            config('whatsapp.phone_number_id')
        );

        try {
            $response = Http::withToken(config('whatsapp.access_token'))
                ->timeout(15)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to' => $toE164,
                    'type' => 'text',
                    'text' => ['body' => $body],
                ]);

            if (! $response->successful()) {
                Log::warning('WhatsApp message send failed', [
                    'to' => $this->mask($toE164),
                    'status' => $response->status(),
                    'error' => $response->json('error.message'),
                ]);
                return false;
            }

            Log::info('WhatsApp message sent', ['to' => $this->mask($toE164)]);
            return true;
        } catch (\Throwable $e) {
            Log::error('WhatsApp message send exception', ['to' => $this->mask($toE164), 'message' => $e->getMessage()]);
            return false;
        }
    }

    public function verifySignature(string $payload, ?string $signatureHeader): bool
    {
        $secret = config('whatsapp.app_secret');

        if (blank($secret)) {
            Log::warning('WHATSAPP_APP_SECRET not set; skipping webhook signature verification.');
            return true;
        }

        if (! $signatureHeader || ! str_starts_with($signatureHeader, 'sha256=')) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signatureHeader);
    }

    private function mask(string $number): string
    {
        return substr($number, 0, 4) . str_repeat('*', max(0, strlen($number) - 6)) . substr($number, -2);
    }
}
