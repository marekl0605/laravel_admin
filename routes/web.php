<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');


Route::get('/api/test/make/admin', function () {
    $user = auth()->user();
    $user->assignRole('administrator');
    return Inertia::render('welcome');
})->name('assign.admin.role');


Route::get('/api/testing', function () {
    return Inertia::render('ApiTester');
})->name('test.api.module');



Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});


Route::middleware(['auth', 'role:administrator'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/admin/users', [AdminController::class, 'users']);
});

Route::middleware(['auth', 'permission:manage-users'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}', [UserController::class, 'update']);
});

Route::middleware(['auth', 'role:student,instructor'])->group(function () {
    Route::get('/courses', [CourseController::class, 'index']);
});


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('/api/v1')->group(function () {

    // ==============================================
    // USERS ROUTES
    // ==============================================
    Route::prefix('users')->group(function () {
        // Standard CRUD operations
        Route::get('/', [UserController::class, 'index']);           // GET /api/v1/users
        Route::post('/', [UserController::class, 'store']);          // POST /api/v1/users
        Route::get('/{user}', [UserController::class, 'show']);      // GET /api/v1/users/{id}
        Route::put('/{user}', [UserController::class, 'update']);    // PUT /api/v1/users/{id}
        Route::patch('/{user}', [UserController::class, 'update']);  // PATCH /api/v1/users/{id}
        Route::delete('/{user}', [UserController::class, 'destroy']); // DELETE /api/v1/users/{id}

        // User relationships
        Route::get('/{user}/companies', [UserController::class, 'companies']); // GET /api/v1/users/{id}/companies
        Route::get('/{user}/people', [UserController::class, 'people']);       // GET /api/v1/users/{id}/people
    });

    // ==============================================
    // PEOPLE ROUTES
    // ==============================================
    Route::prefix('people')->group(function () {
        // Standard CRUD operations
        Route::get('/', [PersonController::class, 'index']);           // GET /api/v1/people
        Route::post('/', [PersonController::class, 'store']);          // POST /api/v1/people
        Route::get('/{person}', [PersonController::class, 'show']);    // GET /api/v1/people/{id}
        Route::put('/{person}', [PersonController::class, 'update']);  // PUT /api/v1/people/{id}
        Route::patch('/{person}', [PersonController::class, 'update']); // PATCH /api/v1/people/{id}
        Route::delete('/{person}', [PersonController::class, 'destroy']); // DELETE /api/v1/people/{id}

        // Person relationships
        Route::get('/{person}/companies', [PersonController::class, 'companies']); // GET /api/v1/people/{id}/companies
        Route::get('/{person}/users', [PersonController::class, 'users']);         // GET /api/v1/people/{id}/users

        // Person-User linking operations
        Route::post('/{person}/users', [PersonController::class, 'linkUser']);     // POST /api/v1/people/{id}/users
        Route::delete('/{person}/users', [PersonController::class, 'unlinkUser']); // DELETE /api/v1/people/{id}/users
    });


    // ==============================================
    // ADDITIONAL HELPER ROUTES (Optional)
    // ==============================================

    // Health check endpoint
    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'message' => 'API is running',
            'version' => 'v1',
            'timestamp' => now()->toISOString()
        ]);
    });

    // API Information endpoint
    Route::get('/info', function () {
        return response()->json([
            'success' => true,
            'api' => [
                'version' => 'v1',
                'name' => 'User Management API',
                'description' => 'RESTful API for managing users, people, and companies',
                'endpoints' => [
                    'users' => '/api/v1/users',
                    'people' => '/api/v1/people',
                    'companies' => '/api/v1/companies'
                ]
            ]
        ]);
    });
});

// Handle undefined API routes
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
        'available_versions' => ['v1']
    ], 404);
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/company.php';
