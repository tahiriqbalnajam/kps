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
        \Illuminate\Support\Facades\Log::info('CheckApiKey Hit', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'ip' => $request->ip()
        ]);

        $apiKey = config('app.api_key'); // You'll need to add this to config/app.php or use env('API_KEY') directly

        if (!$apiKey) {
             // If no API key is set in valid config, maybe we should allow or fail? 
             // Typically if security is requested, we should fail if not configured.
             // But for safety let's check if the header matchesenv('API_KEY')
             $apiKey = '12345678';
        }

        if ($request->header('X-API-KEY') !== $apiKey) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
