<?php

namespace App\Services\Payments;

use InvalidArgumentException;

class PaymentService
{
    public function provider(string $name): PaymentProviderInterface
    {
        return match (strtolower($name)) {
            'esewa' => app(EsewaProvider::class),
            'khalti' => app(KhaltiProvider::class),
            default => throw new InvalidArgumentException("Unsupported payment provider: {$name}"),
        };
    }
}
