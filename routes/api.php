<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LaporanSungaiController;
use App\Http\Controllers\Api\SungaiApiController;
use App\Http\Controllers\Api\SungaiController;
use App\Http\Controllers\Api\UserController;
use Illuminate\http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes
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

// Only users assigned the 'admin' role can access anything inside this group (Spatie)
Route::middleware(['role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard-admin', [AdminController::class, 'stats']);
    Route::get('/users-list', [AdminController::class, 'listUsers']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});

Route::get('/sungais/map', [SungaiController::class, 'map']);

// Menggunakan apiResource sbg efisiensi
Route::apiResource('sungais', SungaiController::class);
Route::apiResource('laporan-sungais', LaporanSungaiController::class);

// Admin and User routes
Route::apiresource('admins', AdminController::class);
Route::apiresource('users', UserController::class);

// Special route for AJAX to get Bounding Box data (GeoJSON)
Route::get('/sungai-bbox', [SungaiApiController::class, 'getWaterways'])->name('api.sungai.bbox');

Route::get('/test-cors', [SungaiController::class, 'index']);

// Sanctum Auth protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user()->load('roles', 'permissions');
    });

    // Laporan Routes
    Route::middleware(['permission:create-items'])->group(function () {
        Route::post('/laporan-sungais', [LaporanSungaiController::class, 'store']);
    });

    Route::middleware(['permission:edit-items'])->group(function () {
        Route::patch('/laporan-sungais/{laporan_sungai}', [LaporanSungaiController::class, 'update']);
    });

    Route::middleware(['permission:delete-items'])->group(function () {
        Route::delete('/laporan-sungais/{laporan_sungai}', [LaporanSungaiController::class, 'destroy']);
    });
});
