<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SlotController;
use App\Http\Controllers\Api\DepositController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\ReportsController;


// 公開路由
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/health', [HealthController::class, 'check']);
    Route::get('/health/db', [HealthController::class, 'checkDb']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Admin Auth
    Route::post('/admin/login', [AdminAuthController::class, 'login']);

    Route::get('/ping', function () {
        return response()->json(['message' => 'pong']);
    });
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
   Route::post('/logout', [AdminAuthController::class, 'logout']);
   Route::get('/me', [AdminAuthController::class, 'me']);
   Route::post('/change-password', [AdminAuthController::class, 'updatePassword']);
   
   Route::get('/dashboard', [AdminController::class, 'dashboard']);
   Route::get('/stats', [ReportsController::class, 'stats']);
   Route::get('/stats/trends', [ReportsController::class, 'trends']);
   Route::get('/stats/games', [ReportsController::class, 'gameStats']);
   Route::get('/users', [AdminController::class, 'users']);
   Route::post('/users/{id}/balance', [AdminController::class, 'updateUserBalance']);
   Route::post('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus']);
   
   Route::get('/game-rounds', [AdminController::class, 'gameRounds']);
   Route::get('/transactions', [AdminController::class, 'transactions']);
});
