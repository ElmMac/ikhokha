<?php


use Illuminate\Support\Facades\Route;
use Elmmac\Ikhokha\Http\Controllers\IkhokhaPaymentController;


// iKhokha routes


Route::post('/initiate-payment', [IkhokhaPaymentController::class, 'initiate'])->name('ikhokha.initiate');
Route::get('/ikhokha/success', [IkhokhaPaymentController::class, 'success'])->name('ikhokha.success');
Route::get('/ikhokha/failed', [IkhokhaPaymentController::class, 'failed'])->name('ikhokha.failed');
Route::get('/ikhokha/cancel', [IkhokhaPaymentController::class, 'cancel'])->name('ikhokha.cancel');
