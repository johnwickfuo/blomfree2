<?php

namespace App\Support\PaymentGateways;

use InvalidArgumentException;

class PaymentGatewayManager
{
    public function __construct(
        private readonly PaystackGateway $paystack,
        private readonly FlutterwaveGateway $flutterwave,
    ) {
    }

    public function for(string $name): PaystackGateway|FlutterwaveGateway
    {
        return match ($name) {
            'paystack' => $this->paystack,
            'flutterwave' => $this->flutterwave,
            default => throw new InvalidArgumentException("Unknown payment gateway: {$name}"),
        };
    }
}
