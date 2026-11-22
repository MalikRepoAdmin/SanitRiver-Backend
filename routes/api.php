<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\LaporanSungaiApiController;
use App\Http\Controllers\Api\LaporanSungaiController;
use App\Http\Controllers\Api\SungaiApiController;
use App\Http\Controllers\Api\SungaiController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 *  Authentication Routes
 */
Route::prefix('auth')->group(function () {
    Route::post('/register-user', [AuthController::class, 'registerUser']);
    Route::post('/register-admin', [AuthController::class, 'registerAdmin']);
    Route::post('/login-user', [AuthController::class, 'loginUser']);
    Route::post('/login-admin', [AuthController::class, 'loginAdmin']);

    // Routes that need to be authenticated by Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout-user', [AuthController::class, 'logoutUser']);
        Route::post('/logout-admin', [AuthController::class, 'logoutAdmin']);
    });
});

/**
 *  Special Routes
 */
// Special route for AJAX to get Bounding Box data (GeoJSON)
Route::get('/sungai-bbox', [SungaiApiController::class, 'getWaterways'])->name('api.sungai.bbox');

// Special route for LaporanSungai
Route::get('/laporan-sungais/latest', [LaporanSungaiApiController::class, 'getLatestLaporans'])->name('laporan.latest');
Route::get('/laporan-sungais/search', [LaporanSungaiApiController::class, 'search'])->name('laporan.search');

// Special route for dashboard content
Route::get('/dashboard-user', [DashboardApiController::class, 'getSummary'])->name('dashboard.user');

/**
 *  Sanctum Auth protected routes
 */
Route::middleware('auth:sanctum')->group(function () {

    // Fetch User's informations
    Route::middleware([
        'permission:show-current-user',
    ])->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user()->load('roles', 'permissions');
        });
    });

    /**
     *  Permission based Routes
     */

    // Laporan Routes
    Route::middleware(['permission:create-laporan_sungais'])->group(function () {
        Route::post('/laporan-sungais', [LaporanSungaiController::class, 'store']);
    });

    Route::middleware(['permission:edit-laporan_sungais'])->group(function () {
        Route::patch('/laporan-sungais/{laporan_sungai}', [LaporanSungaiController::class, 'update']);
    });

    Route::middleware(['permission:delete-laporan_sungais'])->group(function () {
        Route::delete('/laporan-sungais/{laporan_sungai}', [LaporanSungaiController::class, 'destroy']);
    });

    // Sungai Routes
    Route::middleware(['permission:create-sungais'])->group(function () {
        Route::post('/sungais', [SungaiController::class, 'store']);
    });

    Route::middleware(['permission:edit-sungais'])->group(function () {
        Route::patch('/sungais/{sungai}', [SungaiController::class, 'update']);
    });

    Route::middleware(['permission:delete-sungais'])->group(function () {
        Route::delete('/sungais/{sungai}', [SungaiController::class, 'destroy']);
    });

    /**
     *  Role based Routes
     */
    // Only the 'admin' role can access anything inside this group (Spatie)
    // role:admin,admin means role admin and ensure using 'admin' guard
    // regular user with admin token can't use these
    Route::middleware(['auth:admin', 'role:admin,admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard-admin', [AdminController::class, 'stats']);

        // Admin and User Resource routes
        Route::middleware([
            'permission:create-users',
            'permission:show-user',
            'permission:show-current-user',
            'permission:list-users',
            'permission:edit-users',
            'permission:delete-users',
        ])->group(function () {
            Route::apiResource('users', UserController::class);
        });

        Route::middleware([
            'permission:create-admins',
            'permission:show-admin',
            'permission:show-current-admin',
            'permission:list-admins',
            'permission:edit-admins',
            'permission:delete-admins',
        ])->group(function () {
            Route::apiResource('admins', AdminController::class);
        });
    });
});

/**
 *  Resources Routes
 */
// Menggunakan apiResource sbg efisiensi

/**
 *  CRUD Routes
 */
Route::get('/laporan-sungais', [LaporanSungaiController::class, 'index']);
Route::get('/laporan-sungais/{laporan_sungai}', [LaporanSungaiController::class, 'show']);

Route::get('/sungais', [SungaiController::class, 'index']);
Route::get('/sungais/{sungai}', [SungaiController::class, 'show']);
