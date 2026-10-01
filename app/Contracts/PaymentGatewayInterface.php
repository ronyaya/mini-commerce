<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function charge(string $paymentNumber, string $amount): array;
}
