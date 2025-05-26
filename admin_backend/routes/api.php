<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\PersonController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;

Route::get('test', function () {
    return response()->json(['message' => 'API is working']);
});

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:60,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:60,1');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
});

Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::put('/{user}', [UserController::class, 'update']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
    });

    Route::prefix('companies')->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::get('/{company}', [CompanyController::class, 'show']);
        Route::put('/{company}', [CompanyController::class, 'update']);
        Route::delete('/{company}', [CompanyController::class, 'destroy']);
    });

    Route::prefix('people')->group(function () {
        Route::get('/', [PersonController::class, 'index']);
        Route::post('/', [PersonController::class, 'store']);
        Route::get('/{person}', [PersonController::class, 'show']);
        Route::put('/{person}', [PersonController::class, 'update']);
        Route::delete('/{person}', [PersonController::class, 'destroy']);
    });

    Route::prefix('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'index']);
        Route::post('/', [PermissionController::class, 'store']);
        Route::get('/{permission}', [PermissionController::class, 'show']);
        Route::put('/{permission}', [PermissionController::class, 'update']);
        Route::delete('/{permission}', [PermissionController::class, 'destroy']);
    });
});

Route::middleware('auth:api')->prefix('posts')->group(function () {
    Route::get('/', [PostController::class, 'index']);
    Route::post('/', [PostController::class, 'store']);
    Route::get('/{post}', [PostController::class, 'show']);
    Route::put('/{post}', [PostController::class, 'update']);
    Route::delete('/{post}', [PostController::class, 'destroy']);
});