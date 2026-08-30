<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_verification_succeeds_with_the_correct_token(): void
    {
        config(['whatsapp.verify_token' => 'my-verify-token']);

        $response = $this->get('/api/v1/whatsapp/webhook?hub_mode=subscribe&hub_verify_token=my-verify-token&hub_challenge=12345');

        $response->assertStatus(200)->assertSee('12345');
    }

    public function test_webhook_verification_fails_with_the_wrong_token(): void
    {
        config(['whatsapp.verify_token' => 'my-verify-token']);

        $response = $this->getJson('/api/v1/whatsapp/webhook?hub_mode=subscribe&hub_verify_token=wrong&hub_challenge=12345');

        $response->assertStatus(403);
    }

    public function test_incoming_webhook_is_accepted_and_acknowledged(): void
    {
        config(['whatsapp.app_secret' => '']); // signature check skipped when unset, matching local-dev behavior

        $response = $this->postJson('/api/v1/whatsapp/webhook', [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'messages' => [
                                    ['from' => '9779800000000', 'type' => 'text'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_incoming_webhook_with_invalid_signature_is_rejected(): void
    {
        config(['whatsapp.app_secret' => 'shared-secret']);

        $response = $this->postJson('/api/v1/whatsapp/webhook', ['entry' => []], [
            'X-Hub-Signature-256' => 'sha256=not-the-real-signature',
        ]);

        $response->assertStatus(403);
    }
}
