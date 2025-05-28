<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\PersonController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\PasswordResetController;

Route::get('test', function () {
    return response()->json(['message' => 'API is working']);
});

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:60,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:60,1');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
});

Route::post('password/email', [PasswordResetController::class, 'sendResetLink']);
Route::post('password/reset', [PasswordResetController::class, 'reset']);

Route::middleware('auth:api')->group(function () {
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::middleware('admin')->group(function () {
            Route::put('/{user}', [UserController::class, 'update']);
            Route::delete('/{user}', [UserController::class, 'destroy']);
        });
    });

    Route::prefix('companies')->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::get('/{company}', [CompanyController::class, 'show']);
        Route::middleware('admin')->group(function () {
            Route::post('/', [CompanyController::class, 'store']);
            Route::put('/{company}', [CompanyController::class, 'update']);
            Route::delete('/{company}', [CompanyController::class, 'destroy']);
        });
    });

    Route::prefix('people')->group(function () {
        Route::get('/', [PersonController::class, 'index']);
        Route::get('/{person}', [PersonController::class, 'show']);
        Route::middleware('admin')->group(function () {
            Route::post('/', [PersonController::class, 'store']);
            Route::put('/{person}', [PersonController::class, 'update']);
            Route::delete('/{person}', [PersonController::class, 'destroy']);
        });
    });

    Route::prefix('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'index']);
        Route::get('/{permission}', [PermissionController::class, 'show']);
        Route::middleware('admin')->group(function () {
            Route::post('/', [PermissionController::class, 'store']);
            Route::put('/{permission}', [PermissionController::class, 'update']);
            Route::delete('/{permission}', [PermissionController::class, 'destroy']);
        });
    });

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::middleware('admin')->get('/audit-logs', [AuditLogController::class, 'index']);
});