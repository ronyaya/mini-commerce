<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('payment_number')->unique();

            $table->decimal('amount', 12, 2);

            $table->string('status')->default('pending');

            $table->string('idempotency_key')->unique();

            $table->string('provider_transaction_id')
                ->nullable()
                ->unique();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });

        DB::statement('
            ALTER TABLE payments
            ADD CONSTRAINT payments_amount_positive
            CHECK (amount > 0)
        ');

        DB::statement("
            ALTER TABLE payments
            ADD CONSTRAINT payments_status_valid
            CHECK (status IN ('pending', 'processing', 'succeeded', 'failed'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
