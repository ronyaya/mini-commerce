<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        private PaymentGatewayInterface $paymentGateway
    ) {}

    public function charge(
        string $paymentNumber,
        string $amount
    ): array {
        return $this->paymentGateway->charge(
            $paymentNumber,
            $amount
        );
    }

    public function pay(Order $order, string $idempotencyKey): Payment
    {
        $existingPayment = Payment::where(
            'idempotency_key',
            $idempotencyKey
        )->first();

        if ($existingPayment) {
            return $existingPayment;
        }

        try {
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_number' => 'PAY-'.now()->format('YmdHis'),
                'amount' => $order->total_amount,
                'status' => 'processing',
                'idempotency_key' => $idempotencyKey,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            return Payment::where(
                'idempotency_key',
                $idempotencyKey
            )->firstOrFail();
        }

        $startedAt = microtime(true);

        $result = $this->paymentGateway->charge(
            $payment->payment_number,
            $payment->amount
        );

        $durationMs = (microtime(true) - $startedAt) * 1000;

        if ($result['success']) {
            $payment->update([
                'status' => 'succeeded',
                'provider_transaction_id' => $result['provider_transaction_id'],
                'paid_at' => now(),
            ]);

            Log::info('Payment succeeded', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'idempotency_key' => $payment->idempotency_key,
                'provider_transaction_id' => $payment->provider_transaction_id,
                'duration_ms' => round($durationMs, 2),
            ]);
        } else {
            $payment->update([
                'status' => 'failed',
            ]);

            Log::error('Payment failed', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'idempotency_key' => $payment->idempotency_key,
                'provider_transaction_id' => $result['provider_transaction_id'] ?? null,
                'error_code' => $result['error_code'] ?? null,
                'error_message' => $result['error_message'] ?? null,
                'duration_ms' => round($durationMs, 2),
            ]);
        }

        return $payment->refresh();
    }
}
