<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Real eSewa ePay v2 integration (HMAC-signed form submit + server-side
 * status verification). No demo/auto-success fallback: a payment is only
 * ever marked successful after the redirect signature is verified AND
 * eSewa's own status-check API independently confirms COMPLETE.
 */
class EsewaProvider implements PaymentProviderInterface
{
    public function createPayment(Order $order): array
    {
        $config = config('payments.esewa');

        $transactionUuid = $order->order_number . '-' . strtoupper(Str::random(6));
        $amount = (float) $order->subtotal - (float) $order->discount;
        $taxAmount = (float) $order->tax;
        $deliveryCharge = (float) $order->shipping_cost;
        $totalAmount = round($amount + $taxAmount + $deliveryCharge, 2);

        // Guard against drift between the order's stored total and the sum of
        // its parts before it ever reaches the gateway.
        if (abs($totalAmount - (float) $order->total) > 0.01) {
            $totalAmount = (float) $order->total;
            $amount = $totalAmount - $taxAmount - $deliveryCharge;
        }

        $signedFieldNames = 'total_amount,transaction_uuid,product_code';
        $message = "total_amount={$this->fmt($totalAmount)},transaction_uuid={$transactionUuid},product_code={$config['merchant_id']}";
        $signature = base64_encode(hash_hmac('sha256', $message, $config['secret_key'], true));

        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'provider' => 'esewa',
            'transaction_uuid' => $transactionUuid,
            'amount' => $totalAmount,
            'currency' => 'NPR',
            'status' => PaymentTransaction::STATUS_INITIATED,
            'gateway_response' => null,
        ]);

        return [
            'transaction_id' => $transaction->id,
            'action' => $config['gateway_url'],
            'method' => 'POST',
            'fields' => [
                'amount' => $this->fmt($amount),
                'tax_amount' => $this->fmt($taxAmount),
                'total_amount' => $this->fmt($totalAmount),
                'transaction_uuid' => $transactionUuid,
                'product_code' => $config['merchant_id'],
                'product_service_charge' => $this->fmt(0),
                'product_delivery_charge' => $this->fmt($deliveryCharge),
                'success_url' => route('payments.esewa.callback', ['result' => 'success']),
                'failure_url' => route('payments.esewa.callback', ['result' => 'failure']),
                'signed_field_names' => $signedFieldNames,
                'signature' => $signature,
            ],
        ];
    }

    public function verifyPayment(array $payload): PaymentTransaction
    {
        $decoded = $this->decodePayload($payload);

        $transaction = PaymentTransaction::where('provider', 'esewa')
            ->where('transaction_uuid', $decoded['transaction_uuid'] ?? '__missing__')
            ->first();

        if (! $transaction) {
            throw new RuntimeException('Unknown eSewa transaction.');
        }

        // Idempotent: a transaction already settled is never re-processed.
        if ($transaction->status === PaymentTransaction::STATUS_SUCCESS) {
            return $transaction;
        }

        $config = config('payments.esewa');

        if (! $this->signatureValid($decoded, $config['secret_key'])) {
            Log::warning('eSewa signature verification failed', ['transaction_uuid' => $transaction->transaction_uuid]);
            return $this->markFailed($transaction, $decoded, 'invalid_signature');
        }

        if (($decoded['product_code'] ?? null) !== $config['merchant_id']) {
            return $this->markFailed($transaction, $decoded, 'product_code_mismatch');
        }

        if (abs((float) ($decoded['total_amount'] ?? 0) - (float) $transaction->amount) > 0.01) {
            Log::warning('eSewa amount mismatch', [
                'transaction_uuid' => $transaction->transaction_uuid,
                'expected' => (float) $transaction->amount,
                'received' => $decoded['total_amount'] ?? null,
            ]);
            return $this->markFailed($transaction, $decoded, 'amount_mismatch');
        }

        if (($decoded['status'] ?? null) !== 'COMPLETE') {
            return $this->markFailed($transaction, $decoded, 'status_not_complete');
        }

        // Independent server-to-server confirmation, per eSewa's recommended flow.
        $statusCheck = $this->checkStatus($config, $transaction);

        if (($statusCheck['status'] ?? null) !== 'COMPLETE') {
            Log::warning('eSewa status-check did not confirm COMPLETE', [
                'transaction_uuid' => $transaction->transaction_uuid,
                'status_check' => $statusCheck,
            ]);
            return $this->markFailed($transaction, $decoded, 'status_check_failed');
        }

        return DB::transaction(function () use ($transaction, $decoded) {
            $transaction->update([
                'status' => PaymentTransaction::STATUS_SUCCESS,
                'gateway_reference' => $decoded['transaction_code'] ?? null,
                'gateway_response' => $decoded,
            ]);

            $order = $transaction->order;
            $order->update(['payment_status' => Order::PAYMENT_STATUS_PAID]);
            if ($order->status === Order::STATUS_PENDING) {
                $order->recordStatusChange(Order::STATUS_CONFIRMED, 'Payment confirmed via eSewa.');
            }

            return $transaction->fresh();
        });
    }

    private function decodePayload(array $payload): array
    {
        if (isset($payload['data'])) {
            $json = base64_decode((string) $payload['data'], true);
            $decoded = $json ? json_decode($json, true) : null;

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $payload;
    }

    private function signatureValid(array $decoded, string $secret): bool
    {
        $signedFieldNames = $decoded['signed_field_names'] ?? null;
        $signature = $decoded['signature'] ?? null;

        if (! $signedFieldNames || ! $signature) {
            return false;
        }

        $fields = explode(',', $signedFieldNames);
        $parts = [];
        foreach ($fields as $field) {
            if (! array_key_exists($field, $decoded)) {
                return false;
            }
            $parts[] = "{$field}={$decoded[$field]}";
        }

        $expected = base64_encode(hash_hmac('sha256', implode(',', $parts), $secret, true));

        return hash_equals($expected, (string) $signature);
    }

    private function checkStatus(array $config, PaymentTransaction $transaction): array
    {
        try {
            $response = Http::timeout(15)->get($config['status_check_url'], [
                'product_code' => $config['merchant_id'],
                'total_amount' => $this->fmt((float) $transaction->amount),
                'transaction_uuid' => $transaction->transaction_uuid,
            ]);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('eSewa status-check request failed', ['message' => $e->getMessage()]);
            return [];
        }
    }

    private function markFailed(PaymentTransaction $transaction, array $decoded, string $reason): PaymentTransaction
    {
        $transaction->update([
            'status' => PaymentTransaction::STATUS_FAILED,
            'gateway_response' => $decoded + ['failure_reason' => $reason],
        ]);

        return $transaction->fresh();
    }

    private function fmt(float $amount): string
    {
        return rtrim(rtrim(number_format($amount, 2, '.', ''), '0'), '.') ?: '0';
    }
}
