<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/admin')->middleware(['auth', 'role:administrator'])->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('api.admin.dashboard');


    Route::prefix('users')->name('api.admin.users.')->group(function () {

        Route::get('list', [AdminController::class, 'users'])->name('index');
        Route::post('store', [AdminController::class, 'storeUser'])->name('store');
        Route::get('export', [AdminController::class, 'exportUsers'])->name('export');
        Route::post('bulk-actions', [AdminController::class, 'bulkActions'])->name('bulk');

        Route::prefix('{user}')->group(function () {
            Route::get('/', [AdminController::class, 'showUser'])->name('show');
            Route::put('/', [AdminController::class, 'updateUser'])->name('update');
            Route::patch('/', [AdminController::class, 'updateUser'])->name('patch');
            Route::delete('/', [AdminController::class, 'deleteUser'])->name('destroy');
            Route::post('/impersonate', [AdminController::class, 'impersonateUser'])->name('impersonate');
        });
    });

    // Impersonation Routes
    Route::post('/impersonation/stop', [AdminController::class, 'stopImpersonation'])->name('api.admin.impersonation.stop');

    // Role Management Routes
    Route::get('/roles', [AdminController::class, 'roles'])->name('api.admin.roles.index');

    // Permission Management Routes  
    Route::get('/permissions', [AdminController::class, 'permissions'])->name('api.admin.permissions.index');

    // System Settings Routes

    // Security & Logs Routes

    // Reports Routes
});
