<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SendOrderPaidEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 5;

    public array $backoff = [2, 5];

    public function __construct(
        public int $orderId
    ) {}

    public function handle(): void
    {
        DB::transaction(function () {
            $order = Order::query()
                ->whereKey($this->orderId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->paid_email_sent_at !== null) {
                logger()->info('ORDER PAID EMAIL ALREADY SENT', [
                    'order_id' => $order->id,
                ]);

                return;
            }

            logger()->info('SEND ORDER PAID EMAIL', [
                'order_id' => $order->id,
            ]);

            $order->update([
                'paid_email_sent_at' => now(),
            ]);
        });
    }
}
