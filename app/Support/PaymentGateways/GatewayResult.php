<?php

namespace App\Support\PaymentGateways;

class GatewayResult
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public readonly bool $success,
        public readonly ?string $transactionId = null,
        public readonly ?float $amount = null,
        public readonly array $raw = [],
    ) {
    }
}
