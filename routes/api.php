<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\GambarSungaiController;
use App\Http\Controllers\Api\LaporanSungaiController;
use App\Http\Controllers\Api\LaporanSungaiApiController;
use App\Http\Controllers\Api\SungaiController;
use App\Http\Controllers\Api\SungaiApiController;
use App\Http\Controllers\Api\UserController;
use Illuminate\http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register-user', [AuthController::class, 'registerUser']);
    Route::post('/register-admin', [AuthController::class, 'registerAdmin']);
    Route::post('/login-user', [AuthController::class, 'loginUser']);
    Route::post('/login-admin', [AuthController::class, 'loginAdmin']);
    Route::post('/logout-user', [AuthController::class, 'logoutUser']);
    Route::post('/logout-admin', [AuthController::class, 'logoutAdmin']);
});

Route::get('/sungais/map', [SungaiController::class, 'map']);


Route::get('/dashboard/summary', [DashboardApiController::class, 'getSummary']);

// Menggunakan apiResource sbg efisiensi 
Route::apiResource('sungais', SungaiController::class);
Route::apiResource('laporan-sungais', LaporanSungaiController::class);
Route::apiResource('gambar-sungais', GambarSungaiController::class);

// Admin and User routes
Route::apiresource('admins', AdminController::class);
Route::apiresource('users', UserController::class);

// Special route for AJAX to get Bounding Box data (GeoJSON)
Route::get('/sungai-bbox', [SungaiApiController::class, 'getWaterways'])->name('api.sungai.bbox');

Route::get('/test-cors', [SungaiController::class, 'index']);
