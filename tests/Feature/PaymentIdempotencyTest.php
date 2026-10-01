<?php

namespace Tests\Feature;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_idempotency_key_only_charges_once(): void
    {
        $order = new Order;
        $order->user_id = null;
        $order->order_number = 'ORD-TEST-001';
        $order->total_amount = 1000;
        $order->status = 'pending_payment';
        $order->save();

        $gateway = new class implements PaymentGatewayInterface
        {
            public int $chargeCount = 0;

            public function charge(
                string $paymentNumber,
                string $amount
            ): array {
                $this->chargeCount++;

                return [
                    'success' => true,
                    'provider_transaction_id' => 'FAKE-'.$paymentNumber,
                ];
            }
        };

        $service = new PaymentService($gateway);

        $payment1 = $service->pay(
            $order,
            'pay-order-test-001'
        );

        $payment2 = $service->pay(
            $order,
            'pay-order-test-001'
        );

        $this->assertDatabaseCount('payments', 1);

        $this->assertSame(
            $payment1->id,
            $payment2->id
        );

        $this->assertSame(
            1,
            $gateway->chargeCount
        );
    }
}
