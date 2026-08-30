<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentTransaction;

interface PaymentProviderInterface
{
    /**
     * Create a pending PaymentTransaction for the order and return the data
     * the frontend needs to redirect/open the gateway (action URL + form
     * fields, or a hosted payment_url).
     */
    public function createPayment(Order $order): array;

    /**
     * Verify a gateway callback/response server-side and return the
     * (idempotently) updated PaymentTransaction. Never trusts amount or
     * status from the request — always re-derives or re-checks against the
     * gateway and the order it was created for.
     */
    public function verifyPayment(array $payload): PaymentTransaction;
}
