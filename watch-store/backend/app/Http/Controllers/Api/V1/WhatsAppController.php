<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    public function __construct(private readonly WhatsAppService $whatsApp)
    {
    }

    /**
     * Meta's webhook verification handshake: GET with hub.mode=subscribe,
     * hub.verify_token, hub.challenge. Must echo back hub.challenge as plain
     * text when the verify token matches WHATSAPP_VERIFY_TOKEN.
     */
    public function verify(Request $request): Response|JsonResponse
    {
        $mode = $request->query('hub_mode', $request->query('hub.mode'));
        $token = $request->query('hub_verify_token', $request->query('hub.verify_token'));
        $challenge = $request->query('hub_challenge', $request->query('hub.challenge'));

        $expected = config('whatsapp.verify_token');

        if ($mode === 'subscribe' && $expected !== '' && hash_equals((string) $expected, (string) $token)) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response()->json(['success' => false, 'message' => 'Verification failed'], 403);
    }

    /**
     * Incoming message/status webhook deliveries from Meta. Verifies the
     * X-Hub-Signature-256 header (when WHATSAPP_APP_SECRET is set), logs the
     * event, and always returns 200 quickly — Meta retries aggressively on
     * non-200 responses, so slow/failing processing must never block the ack.
     */
    public function handle(Request $request): JsonResponse
    {
        $valid = $this->whatsApp->verifySignature(
            $request->getContent(),
            $request->header('X-Hub-Signature-256')
        );

        if (! $valid) {
            Log::warning('WhatsApp webhook signature verification failed.');
            return response()->json(['success' => false], 403);
        }

        $entries = $request->input('entry', []);

        foreach ($entries as $entry) {
            foreach (($entry['changes'] ?? []) as $change) {
                $value = $change['value'] ?? [];

                foreach (($value['messages'] ?? []) as $message) {
                    Log::info('WhatsApp inbound message received', [
                        'from' => substr((string) ($message['from'] ?? ''), 0, 6) . '***',
                        'type' => $message['type'] ?? 'unknown',
                    ]);
                }

                foreach (($value['statuses'] ?? []) as $status) {
                    Log::info('WhatsApp delivery status update', [
                        'status' => $status['status'] ?? 'unknown',
                    ]);
                }
            }
        }

        return response()->json(['success' => true]);
    }
}
