<?php

use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\WalletController;
use App\Http\Controllers\Webhooks\FapshiWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/fapshi', [FapshiWebhookController::class, 'handle'])->name('webhooks.fapshi');

Route::middleware(['api', 'auth.api', 'throttle:60,1'])->prefix('v1')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::get('/wallet', [WalletController::class, 'show']);
});
