<?php

namespace App\Http\Controllers\Api;

use IlluminateSupportFacadesHttp;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
{
    public function testResponseTime(Request $request)
    {
        $start = microtime(true);
        
        // 模擬一些處理
        usleep(10000); // 10ms delay
        
        $end = microtime(true);
        $responseTime = ($end - $start) * 1000; // in ms
        
        return response()->json([
            'response_time_ms' => $responseTime,
            'status' => 'ok'
        ]);
    }
    
    public function benchmarkApi(Request $request)
    {
        $endpoint = $request->get('endpoint', '/api/health');
        $iterations = $request->get('iterations', 10);
        
        $times = [];
        $totalTime = 0;
        
        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            
            // 這裡應該實際呼叫 API，但為了簡單，使用內部呼叫
            $response = $this->callInternalEndpoint($endpoint);
            
            $end = microtime(true);
            $time = ($end - $start) * 1000;
            $times[] = $time;
            $totalTime += $time;
        }
        
        $avgTime = $totalTime / $iterations;
        $minTime = min($times);
        $maxTime = max($times);
        
        return response()->json([
            'endpoint' => $endpoint,
            'iterations' => $iterations,
            'average_time_ms' => $avgTime,
            'min_time_ms' => $minTime,
            'max_time_ms' => $maxTime,
            'times' => $times,
            'report' => $this->generateReport($avgTime, $minTime, $maxTime)
        ]);
    }
    
    private function callInternalEndpoint($endpoint)
    {
        // 簡化的內部呼叫模擬
        switch ($endpoint) {
            case '/api/health':
                return ['status' => 'ok'];
            case '/api/health/db':
                DB::connection()->getPdo();
                return ['status' => 'ok'];
            default:
                return ['status' => 'unknown'];
        }
    }
    
    private function generateReport($avg, $min, $max)
    {
        $report = [];
        
        if ($avg < 50) {
            $report[] = '響應速度優秀';
        } elseif ($avg < 200) {
            $report[] = '響應速度良好';
        } else {
            $report[] = '響應速度需要優化';
        }
        
        if ($max - $min > 100) {
            $report[] = '響應時間波動大，建議檢查伺服器負載';
        }
        
        return $report;
    }
}
