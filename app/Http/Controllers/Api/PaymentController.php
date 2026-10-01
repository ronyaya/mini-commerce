<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function pay(
        Request $request,
        Order $order,
        PaymentService $paymentService
    ): JsonResponse {
        $payment = $paymentService->pay(
            $order,
            $request->input('idempotency_key')
        );

        return response()->json([
            'payment_id' => $payment->id,
            'status' => $payment->status,
            'idempotency_key' => $payment->idempotency_key,
        ]);
    }

    public function show(Payment $payment): JsonResponse
    {
        return response()->json([
            'payment_id' => $payment->id,
            'order_id' => $payment->order_id,
            'amount' => $payment->amount,
            'status' => $payment->status,
            'provider_transaction_id' => $payment->provider_transaction_id,
            'paid_at' => $payment->paid_at,
        ]);
    }
}
