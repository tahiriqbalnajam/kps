<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;

class IdentifyTenant
{
    public function handle($request, Closure $next)
    {
        $host = $request->getHost();
        $tenant = Tenant::where('domain', $host)->first();

        if (!$tenant) {
            abort(404, 'Tenant not found.');
        }

        // Set tenant context
        app()->instance('tenant', $tenant);

        return $next($request);
    }
}