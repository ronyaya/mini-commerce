<?php

use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);

Route::get('/products/{product}/inventory', [InventoryController::class, 'show']);
Route::post('/inventories/{inventory}/adjust', [InventoryController::class, 'adjust']);
Route::post('/inventories/{inventory}/reserve', [InventoryController::class, 'reserve']);
Route::post('/inventories/{inventory}/reserve-with-lock', [InventoryController::class, 'reserveWithLock']);

Route::post('/orders', [OrderController::class, 'store']);
Route::post('/orders/{order}/pay', [PaymentController::class, 'pay']);

Route::get('/payments/{payment}', [PaymentController::class, 'show']);

Route::post('/webhooks/payments', [PaymentWebhookController::class, 'handle']);
