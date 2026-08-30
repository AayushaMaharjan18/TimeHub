<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Real Khalti ePayment (KPG v2) integration: server-side initiate + lookup.
 * A missing secret key disables live payments entirely rather than falling
 * back to a fake success — there is no demo-mode auto-approval here.
 */
class KhaltiProvider implements PaymentProviderInterface
{
    public function createPayment(Order $order): array
    {
        $config = config('payments.khalti');

        if (blank($config['secret_key'])) {
            throw new RuntimeException('Khalti is not configured: KHALTI_SECRET_KEY is missing.');
        }

        $amountPaisa = (int) round((float) $order->total * 100);

        $response = Http::withHeaders([
            'Authorization' => 'Key ' . $config['secret_key'],
            'Content-Type' => 'application/json',
        ])->timeout(15)->post($config['base_url'] . '/epayment/initiate/', [
            'return_url' => route('payments.khalti.callback'),
            'website_url' => config('app.url'),
            'amount' => $amountPaisa,
            'purchase_order_id' => $order->order_number,
            'purchase_order_name' => 'Order ' . $order->order_number,
            'customer_info' => [
                'name' => $order->shipping_name,
                'phone' => $order->shipping_phone,
            ],
        ]);

        if (! $response->successful()) {
            Log::error('Khalti initiate failed', ['status' => $response->status(), 'body' => $response->json()]);
            throw new RuntimeException('Failed to initiate Khalti payment.');
        }

        $data = $response->json();

        if (empty($data['pidx']) || empty($data['payment_url'])) {
            throw new RuntimeException('Khalti did not return a valid payment session.');
        }

        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'provider' => 'khalti',
            'transaction_uuid' => $data['pidx'],
            'amount' => $order->total,
            'currency' => 'NPR',
            'status' => PaymentTransaction::STATUS_INITIATED,
            'gateway_response' => $data,
        ]);

        return [
            'transaction_id' => $transaction->id,
            'payment_url' => $data['payment_url'],
            'pidx' => $data['pidx'],
        ];
    }

    public function verifyPayment(array $payload): PaymentTransaction
    {
        $pidx = $payload['pidx'] ?? null;

        if (! $pidx) {
            throw new RuntimeException('Missing pidx for Khalti verification.');
        }

        $transaction = PaymentTransaction::where('provider', 'khalti')
            ->where('transaction_uuid', $pidx)
            ->first();

        if (! $transaction) {
            throw new RuntimeException('Unknown Khalti transaction.');
        }

        if ($transaction->status === PaymentTransaction::STATUS_SUCCESS) {
            return $transaction;
        }

        $config = config('payments.khalti');

        if (blank($config['secret_key'])) {
            throw new RuntimeException('Khalti is not configured: KHALTI_SECRET_KEY is missing.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Key ' . $config['secret_key'],
            'Content-Type' => 'application/json',
        ])->timeout(15)->post($config['base_url'] . '/epayment/lookup/', [
            'pidx' => $pidx,
        ]);

        if (! $response->successful()) {
            Log::error('Khalti lookup failed', ['status' => $response->status(), 'pidx' => $pidx]);
            return $this->markFailed($transaction, ['error' => 'lookup_request_failed']);
        }

        $data = $response->json();
        $status = $data['status'] ?? null;

        if ($status !== 'Completed') {
            return $this->markStatus($transaction, $data, $this->mapStatus($status));
        }

        $expectedPaisa = (int) round((float) $transaction->amount * 100);
        $receivedPaisa = (int) ($data['total_amount'] ?? 0);

        if ($receivedPaisa !== $expectedPaisa) {
            Log::warning('Khalti amount mismatch', [
                'pidx' => $pidx,
                'expected_paisa' => $expectedPaisa,
                'received_paisa' => $receivedPaisa,
            ]);
            return $this->markFailed($transaction, $data + ['failure_reason' => 'amount_mismatch']);
        }

        return DB::transaction(function () use ($transaction, $data) {
            $transaction->update([
                'status' => PaymentTransaction::STATUS_SUCCESS,
                'gateway_reference' => $data['transaction_id'] ?? null,
                'gateway_response' => $data,
            ]);

            $order = $transaction->order;
            $order->update(['payment_status' => Order::PAYMENT_STATUS_PAID]);
            if ($order->status === Order::STATUS_PENDING) {
                $order->recordStatusChange(Order::STATUS_CONFIRMED, 'Payment confirmed via Khalti.');
            }

            return $transaction->fresh();
        });
    }

    private function mapStatus(?string $khaltiStatus): string
    {
        return match ($khaltiStatus) {
            'Pending' => PaymentTransaction::STATUS_PENDING,
            'User canceled' => PaymentTransaction::STATUS_CANCELLED,
            'Refunded', 'Partially Refunded' => PaymentTransaction::STATUS_REFUNDED,
            default => PaymentTransaction::STATUS_FAILED,
        };
    }

    private function markStatus(PaymentTransaction $transaction, array $data, string $status): PaymentTransaction
    {
        $transaction->update([
            'status' => $status,
            'gateway_response' => $data,
        ]);

        return $transaction->fresh();
    }

    private function markFailed(PaymentTransaction $transaction, array $data): PaymentTransaction
    {
        return $this->markStatus($transaction, $data, PaymentTransaction::STATUS_FAILED);
    }
}
