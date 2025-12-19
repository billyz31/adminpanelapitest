<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HealthController extends Controller
{
    // 基本 API 連通性檢查
    public function check()
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'API is reachable',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    // 資料庫連接檢查
    public function checkDb()
    {
        try {
            // 嘗試執行一個簡單的查詢
            DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();
            
            // 檢查是否能查詢 users 表
            $userCount = DB::table('users')->count();

            return response()->json([
                'status' => 'ok',
                'database' => $dbName,
                'user_count' => $userCount,
                'message' => 'Database connection successful',
            ]);
        } catch (\Exception $e) {
            Log::error('Health check failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
