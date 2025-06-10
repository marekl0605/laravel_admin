<?php

use App\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

// ==============================================
// COMPANIES ROUTES
// ==============================================

Route::prefix('/api/v1')->group(function () {

    Route::prefix('companies')->group(function () {
        // Standard CRUD operations
        Route::get('/', [CompanyController::class, 'index']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::get('/{company}', [CompanyController::class, 'show']);
        Route::put('/{company}', [CompanyController::class, 'update']);
        Route::patch('/{company}', [CompanyController::class, 'update']);
        Route::delete('/{company}', [CompanyController::class, 'destroy']);

        // Company relationships
        Route::get('/{company}/users', [CompanyController::class, 'users']);
        Route::get('/{company}/people', [CompanyController::class, 'people']);

        // Specialized company endpoints
        Route::get('/{company}/users/authenticated', [CompanyController::class, 'authenticatedUsers']);
        Route::get('/{company}/people/authenticated', [CompanyController::class, 'authenticatedPeople']);
        Route::get('/{company}/people/non-authenticated', [CompanyController::class, 'nonAuthenticatedPeople']);
    });
});
