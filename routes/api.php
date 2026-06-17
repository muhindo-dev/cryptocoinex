<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TradingApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Cryptocoinex API v1 (token auth via Laravel Sanctum)
|--------------------------------------------------------------------------
| For a future mobile app. Obtain a token from /register or /login, then send
| it as `Authorization: Bearer <token>` on protected endpoints.
*/
Route::prefix('v1')->group(function () {
    // Public
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);

        Route::get('assets', [TradingApiController::class, 'assets']);
        Route::get('wallet', [TradingApiController::class, 'wallet']);
        Route::get('price', [TradingApiController::class, 'price']);

        Route::post('trade', [TradingApiController::class, 'place']);
        Route::get('trade/{trade}', [TradingApiController::class, 'show']);
        Route::get('history', [TradingApiController::class, 'history']);
    });
});
