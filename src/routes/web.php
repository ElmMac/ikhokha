<?php


use Illuminate\Support\Facades\Route;
use Elmmac\Ikhokha\Http\Controllers\IkhokhaPaymentController;


// iKhokha routes


Route::post('/ikhokha-initiate', [IkhokhaPaymentController::class, 'initiate'])->name(name: 'ikhokha.initiate');
// Route::post('/api/ikhokha/webhook', [IkhokhaPaymentController::class, 'webook'])->name(name: 'ikhokha.webhook'); // taken care of in api.php
Route::get('/ikhokha/success', [IkhokhaPaymentController::class, 'success'])->name(name: 'ikhokha.success');
Route::get('/ikhokha/failed', [IkhokhaPaymentController::class, 'failed'])->name(name: 'ikhokha.failed');
Route::get('/ikhokha/cancel', [IkhokhaPaymentController::class, 'cancel'])->name(name: 'ikhokha.cancel');

Route::get('/test-pay', function () {
    return '
        <form method="POST" action="/ikhokha-initiate">
            ' . csrf_field() . '
            <input type="number" name="amount" value="10">
            <button type="submit">Pay with iKhokha</button>
        </form>
    ';
});
