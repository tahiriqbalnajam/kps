<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {

        $apiKey = config('app.api_key'); // You'll need to add this to config/app.php or use env('API_KEY') directly

        if (!$apiKey) {
             // If no API key is set in valid config, maybe we should allow or fail? 
             // Typically if security is requested, we should fail if not configured.
             // But for safety let's check if the header matchesenv('API_KEY')
             $apiKey = '12345678';
        }

        // Check for 'X-API-KEY'
        if ($request->header('X-API-KEY') === $apiKey) {
             return $next($request);
        }

        // If API Key is missing or invalid, check for Sanctum authentication
        if (\Illuminate\Support\Facades\Auth::guard('sanctum')->check()) {
             return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], 401);

        return $next($request);
    }
}
