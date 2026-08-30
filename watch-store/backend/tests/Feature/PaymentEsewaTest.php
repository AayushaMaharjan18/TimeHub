<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\Payments\EsewaProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentEsewaTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(): Order
    {
        return Order::create([
            'order_number' => 'ORD-ESEWA1',
            'subtotal' => 1000,
            'shipping_cost' => 0,
            'tax' => 0,
            'discount' => 0,
            'total' => 1000,
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'esewa',
            'payment_status' => Order::PAYMENT_STATUS_PENDING,
            'shipping_name' => 'Test Buyer',
            'shipping_phone' => '9800000000',
            'shipping_district' => 'Kathmandu',
            'shipping_municipality' => 'Kathmandu',
            'shipping_ward' => '1',
            'shipping_street' => 'Test Street',
        ]);
    }

    private function sign(string $secret, array $fields, string $signedFieldNames): string
    {
        $parts = array_map(fn ($f) => "{$f}={$fields[$f]}", explode(',', $signedFieldNames));
        return base64_encode(hash_hmac('sha256', implode(',', $parts), $secret, true));
    }

    public function test_successful_esewa_payment_is_verified_and_marks_order_paid(): void
    {
        $order = $this->makeOrder();
        $provider = app(EsewaProvider::class);
        $payment = $provider->createPayment($order);

        $secret = config('payments.esewa.secret_key');
        $fields = [
            'transaction_code' => 'TXN123',
            'status' => 'COMPLETE',
            'total_amount' => $payment['fields']['total_amount'],
            'transaction_uuid' => $payment['fields']['transaction_uuid'],
            'product_code' => $payment['fields']['product_code'],
        ];
        $signedFieldNames = 'transaction_code,status,total_amount,transaction_uuid,product_code';
        $fields['signed_field_names'] = $signedFieldNames;
        $fields['signature'] = $this->sign($secret, $fields, $signedFieldNames);

        Http::fake([
            config('payments.esewa.status_check_url') . '*' => Http::response(['status' => 'COMPLETE'], 200),
        ]);

        $data = base64_encode(json_encode($fields));
        $transaction = $provider->verifyPayment(['data' => $data]);

        $this->assertSame(PaymentTransaction::STATUS_SUCCESS, $transaction->status);
        $this->assertSame(Order::PAYMENT_STATUS_PAID, $order->fresh()->payment_status);
        $this->assertSame(Order::STATUS_CONFIRMED, $order->fresh()->status);
    }

    public function test_esewa_payment_with_invalid_signature_is_rejected(): void
    {
        $order = $this->makeOrder();
        $provider = app(EsewaProvider::class);
        $payment = $provider->createPayment($order);

        $fields = [
            'transaction_code' => 'TXN999',
            'status' => 'COMPLETE',
            'total_amount' => $payment['fields']['total_amount'],
            'transaction_uuid' => $payment['fields']['transaction_uuid'],
            'product_code' => $payment['fields']['product_code'],
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
            'signature' => base64_encode('not-a-real-signature'),
        ];

        $transaction = $provider->verifyPayment(['data' => base64_encode(json_encode($fields))]);

        $this->assertSame(PaymentTransaction::STATUS_FAILED, $transaction->status);
        $this->assertSame(Order::PAYMENT_STATUS_PENDING, $order->fresh()->payment_status);
    }

    public function test_esewa_payment_with_tampered_amount_is_rejected(): void
    {
        $order = $this->makeOrder();
        $provider = app(EsewaProvider::class);
        $payment = $provider->createPayment($order);
        $secret = config('payments.esewa.secret_key');

        // Attacker changes total_amount but can't forge a matching signature
        // for the tampered value without the secret key.
        $fields = [
            'total_amount' => '1', // tampered down from the real 1000
            'transaction_uuid' => $payment['fields']['transaction_uuid'],
            'product_code' => $payment['fields']['product_code'],
        ];
        $signedFieldNames = 'total_amount,transaction_uuid,product_code';
        $fields['signed_field_names'] = $signedFieldNames;
        $fields['signature'] = $this->sign($secret, $fields, $signedFieldNames);
        $fields['status'] = 'COMPLETE';

        $transaction = $provider->verifyPayment(['data' => base64_encode(json_encode($fields))]);

        $this->assertSame(PaymentTransaction::STATUS_FAILED, $transaction->status);
        $this->assertSame(Order::PAYMENT_STATUS_PENDING, $order->fresh()->payment_status);
    }

    public function test_duplicate_esewa_verification_is_idempotent(): void
    {
        $order = $this->makeOrder();
        $provider = app(EsewaProvider::class);
        $payment = $provider->createPayment($order);
        $secret = config('payments.esewa.secret_key');

        $fields = [
            'transaction_code' => 'TXN777',
            'status' => 'COMPLETE',
            'total_amount' => $payment['fields']['total_amount'],
            'transaction_uuid' => $payment['fields']['transaction_uuid'],
            'product_code' => $payment['fields']['product_code'],
        ];
        $signedFieldNames = 'transaction_code,status,total_amount,transaction_uuid,product_code';
        $fields['signed_field_names'] = $signedFieldNames;
        $fields['signature'] = $this->sign($secret, $fields, $signedFieldNames);

        Http::fake([
            config('payments.esewa.status_check_url') . '*' => Http::response(['status' => 'COMPLETE'], 200),
        ]);

        $data = base64_encode(json_encode($fields));
        $provider->verifyPayment(['data' => $data]);
        $provider->verifyPayment(['data' => $data]);

        // Only one status-history "confirmed" entry, not two, even though
        // verify was called twice with an identical valid payload.
        $this->assertSame(1, $order->statusHistories()->where('status', Order::STATUS_CONFIRMED)->count());
        Http::assertSentCount(1); // second call short-circuits before hitting the gateway again
    }
}
