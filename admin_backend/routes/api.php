<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:60,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:60,1');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
});

Route::get('test', function () {
    return response()->json(['message' => 'API is working']);
});