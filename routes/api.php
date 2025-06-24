<?php

use Application\Api\User\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Application\Api\Wallet\Controllers\WalletController;
use Application\Api\Payment\Controllers\TransactionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/test', [UserController::class, "index"]);

// Wallet Routes
Route::middleware('auth:sanctum')->group(function () {
    // Wallet endpoints
    // Route::get('/wallet', [WalletController::class, 'show']);
    // Route::post('/wallet/top-up', [WalletController::class, 'topUp']);
    // Route::post('/wallet/transfer', [WalletController::class, 'transfer']);

    // Transaction endpoints
    // Route::get('/transactions', [TransactionController::class, 'index']);
    // Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
});

require __DIR__.'/api/auth.php';
require __DIR__.'/api/site.php';