<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;

class FakePaymentGateway implements PaymentGatewayInterface
{
    public function __construct(
        private bool $shouldSucceed = true
    ) {}

    public function charge(
        string $paymentNumber,
        string $amount
    ): array {
        if ($this->shouldSucceed) {
            return [
                'success' => true,
                'provider_transaction_id' => 'FAKE-' . $paymentNumber,
            ];
        }

        return [
            'success' => false,
            'provider_transaction_id' => null,
            'error_code' => 'CARD_DECLINED',
            'error_message' => 'Card was declined.',
        ];
    }
}
