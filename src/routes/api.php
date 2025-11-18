<?php

use Illuminate\Support\Facades\Route;
use Elmmac\Ikhokha\Http\Controllers\IkhokhaPaymentController;

$webhookPath = config(key: 'ikhokha.webhook_endpoint', default: '/api/ikhokha/webhook');

// Make the webhook route accept POST only; no auth by default but configurable
Route::post(uri: $webhookPath, action: [IkhokhaPaymentController::class, 'webhook'])->name('ikhokha.webhook');
