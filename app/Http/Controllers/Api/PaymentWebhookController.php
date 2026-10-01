<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $signature = $request->header('X-Payment-Signature');

        $expectedSignature = hash_hmac(
            'sha256',
            $request->getContent(),
            'fake-payment-secret'
        );

        if (! hash_equals($expectedSignature, $signature ?? '')) {
            return response()->json([
                'message' => 'Invalid signature.',
            ], 401);
        }
        $payment = Payment::where(
            'provider_transaction_id',
            $request->input('provider_transaction_id')
        )->firstOrFail();

        if ($payment->status === 'succeeded') {
            return response()->json([
                'message' => 'Webhook already processed.',
            ]);
        }

        if ($request->input('status') === 'succeeded') {
            DB::transaction(function () use ($payment) {
                $payment->update([
                    'status' => 'succeeded',
                    'paid_at' => now(),
                ]);

                $payment->order->update([
                    'status' => 'paid',
                ]);
            });

            return response()->json([
                'message' => 'Payment succeeded.',
            ]);
        }

        return response()->json([
            'message' => 'Webhook verified.',
        ]);
    }
}
