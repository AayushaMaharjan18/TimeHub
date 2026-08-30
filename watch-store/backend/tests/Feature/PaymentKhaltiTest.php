<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\Payments\KhaltiProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class PaymentKhaltiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['payments.khalti.secret_key' => 'test_secret_key']);
    }

    private function makeOrder(float $total = 1000): Order
    {
        return Order::create([
            'order_number' => 'ORD-KHALTI1',
            'subtotal' => $total,
            'shipping_cost' => 0,
            'tax' => 0,
            'discount' => 0,
            'total' => $total,
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'khalti',
            'payment_status' => Order::PAYMENT_STATUS_PENDING,
            'shipping_name' => 'Test Buyer',
            'shipping_phone' => '9800000000',
            'shipping_district' => 'Kathmandu',
            'shipping_municipality' => 'Kathmandu',
            'shipping_ward' => '1',
            'shipping_street' => 'Test Street',
        ]);
    }

    public function test_initiate_without_secret_key_throws_instead_of_faking_success(): void
    {
        config(['payments.khalti.secret_key' => '']);
        $order = $this->makeOrder();

        $this->expectException(RuntimeException::class);
        app(KhaltiProvider::class)->createPayment($order);
    }

    public function test_successful_khalti_payment_is_verified_and_marks_order_paid(): void
    {
        Http::fake([
            'dev.khalti.com/api/v2/epayment/initiate/*' => Http::response([
                'pidx' => 'pidx_abc123',
                'payment_url' => 'https://test-pay.khalti.com/?pidx=pidx_abc123',
            ], 200),
            'dev.khalti.com/api/v2/epayment/lookup/*' => Http::response([
                'pidx' => 'pidx_abc123',
                'total_amount' => 100000,
                'status' => 'Completed',
                'transaction_id' => 'khalti_txn_1',
            ], 200),
        ]);

        $order = $this->makeOrder(1000);
        $provider = app(KhaltiProvider::class);
        $payment = $provider->createPayment($order);

        $this->assertSame('pidx_abc123', $payment['pidx']);

        $transaction = $provider->verifyPayment(['pidx' => 'pidx_abc123']);

        $this->assertSame(PaymentTransaction::STATUS_SUCCESS, $transaction->status);
        $this->assertSame(Order::PAYMENT_STATUS_PAID, $order->fresh()->payment_status);
        $this->assertSame(Order::STATUS_CONFIRMED, $order->fresh()->status);
    }

    public function test_khalti_payment_with_wrong_amount_is_rejected(): void
    {
        Http::fake([
            'dev.khalti.com/api/v2/epayment/initiate/*' => Http::response([
                'pidx' => 'pidx_wrongamt',
                'payment_url' => 'https://test-pay.khalti.com/?pidx=pidx_wrongamt',
            ], 200),
            'dev.khalti.com/api/v2/epayment/lookup/*' => Http::response([
                'pidx' => 'pidx_wrongamt',
                'total_amount' => 1, // gateway reports far less than the order total
                'status' => 'Completed',
                'transaction_id' => 'khalti_txn_2',
            ], 200),
        ]);

        $order = $this->makeOrder(1000);
        $provider = app(KhaltiProvider::class);
        $provider->createPayment($order);

        $transaction = $provider->verifyPayment(['pidx' => 'pidx_wrongamt']);

        $this->assertSame(PaymentTransaction::STATUS_FAILED, $transaction->status);
        $this->assertSame(Order::PAYMENT_STATUS_PENDING, $order->fresh()->payment_status);
    }

    public function test_khalti_pending_payment_does_not_mark_order_paid(): void
    {
        Http::fake([
            'dev.khalti.com/api/v2/epayment/initiate/*' => Http::response([
                'pidx' => 'pidx_pending',
                'payment_url' => 'https://test-pay.khalti.com/?pidx=pidx_pending',
            ], 200),
            'dev.khalti.com/api/v2/epayment/lookup/*' => Http::response([
                'pidx' => 'pidx_pending',
                'total_amount' => 100000,
                'status' => 'Pending',
            ], 200),
        ]);

        $order = $this->makeOrder(1000);
        $provider = app(KhaltiProvider::class);
        $provider->createPayment($order);

        $transaction = $provider->verifyPayment(['pidx' => 'pidx_pending']);

        $this->assertSame(PaymentTransaction::STATUS_PENDING, $transaction->status);
        $this->assertSame(Order::PAYMENT_STATUS_PENDING, $order->fresh()->payment_status);
    }

    public function test_duplicate_khalti_verification_is_idempotent_and_does_not_recall_gateway(): void
    {
        Http::fake([
            'dev.khalti.com/api/v2/epayment/initiate/*' => Http::response([
                'pidx' => 'pidx_dup',
                'payment_url' => 'https://test-pay.khalti.com/?pidx=pidx_dup',
            ], 200),
            'dev.khalti.com/api/v2/epayment/lookup/*' => Http::response([
                'pidx' => 'pidx_dup',
                'total_amount' => 100000,
                'status' => 'Completed',
                'transaction_id' => 'khalti_txn_3',
            ], 200),
        ]);

        $order = $this->makeOrder(1000);
        $provider = app(KhaltiProvider::class);
        $provider->createPayment($order);

        $provider->verifyPayment(['pidx' => 'pidx_dup']);
        $provider->verifyPayment(['pidx' => 'pidx_dup']);

        $this->assertSame(1, $order->statusHistories()->where('status', Order::STATUS_CONFIRMED)->count());
        Http::assertSentCount(2); // initiate + one lookup; the second verify short-circuits
    }
}
