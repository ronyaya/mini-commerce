<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Services\Payments\FakePaymentGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, FakePaymentGateway::class);
        /*
        $this->app->bind(
            PaymentGatewayInterface::class,
            fn() => new FakePaymentGateway(false)
        );
        */
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
