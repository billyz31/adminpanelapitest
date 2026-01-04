<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HealthController extends Controller
{
    public function check(Request $request)
    {
        // Attempt to determine the web server software
        // When running behind a proxy like Nginx, SERVER_SOFTWARE might report the PHP server (if using artisan serve)
        // or PHP-FPM. We can look for specific headers or default to Nginx if we know the architecture.
        $software = $request->server('SERVER_SOFTWARE', 'Unknown');
        
        // Check for Nginx in headers if not in software string
        if (strpos(strtolower($software), 'nginx') === false) {
             // If we are in the docker environment with Nginx, we can assume Nginx or check headers
             // But for now, let's trust the environment.
             // If it says "Development Server" it's likely artisan serve.
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'API is reachable',
            'server_info' => [
                'software' => $software,
                'php_version' => phpversion(),
                'laravel_version' => app()->version(),
                'client_ip' => $request->ip(), // Handles X-Forwarded-For automatically in Laravel if configured
                'server_ip' => $request->server('SERVER_ADDR') ?? gethostbyname(gethostname()) ?: 'Unknown',
                'host' => $request->getHost(),
                'headers' => config('app.debug') ? $request->headers->all() : [], // Optional: for debugging
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function checkDb()
    {
        try {
            // Check connection
            DB::connection()->getPdo();
            
            // Get database size and other stats if possible
            $dbName = DB::connection()->getDatabaseName();
            $version = DB::select('select version()')[0]->version;
            $userCount = DB::table('users')->count();

            return response()->json([
                'status' => 'ok',
                'message' => 'Database connection successful',
                'database' => $dbName,
                'version' => $version,
                'user_count' => $userCount,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Database connection failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }
}
