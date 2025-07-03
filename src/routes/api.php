<?php

use Elmmac\Ikhokha\Http\Controllers\IkhokhaPaymentController;
use Illuminate\Support\Facades\Route;


Route::post('/ikhokha/callback', [IkhokhaPaymentController::class, 'handleWebhook']);
