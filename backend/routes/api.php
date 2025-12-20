<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SlotController;
use App\Http\Controllers\Api\DepositController;
use App\Http\Controllers\Api\HealthController;


// 公開路由
Route::get('/health', [HealthController::class, 'check']);
Route::get('/health/db', [HealthController::class, 'checkDb']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

// 需要驗證的路由
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // 資金相關
    Route::post('/deposit', [DepositController::class, 'store']);
    
    // Slot Game
    Route::post('/slot/spin', [SlotController::class, 'spin']);
});

// 管理員路由
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/users', [AdminController::class, 'getUsers']);
    Route::get('/stats', [AdminController::class, 'getSystemStats']);
});


