<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SubdomainDatabaseSwitcher
{
    public function handle(Request $request, Closure $next)
    {
        // Get the subdomain
        $host = $request->getHost(); // e.g., sub.example.com
        $subdomain = explode('.', $host)[0]; // Extract subdomain

        $details = DB::table('databases')->where('subdomain', $subdomain)->first();

        // If subdomain exists, switch database
        if ($details) {
            Config::set('database.connections.dynamic', [
                'driver'    => 'mysql',
                'host'      => env('DB_HOST', '127.0.0.1'),
                'port'      => env('DB_PORT', '3306'),
                'database'  => $details->db,
                'username'  => $details->username,
                'password'  => $details->password,
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
                'strict'    => true,
                'engine'    => null,
            ]);

            // Set Laravel to use the new connection
            Config::set('database.default', 'dynamic');

            // Clear and reconnect database connection
            DB::purge('dynamic');
            DB::reconnect('dynamic');
        }

        else {
            Config::set('database.connections.dynamic', [
                'driver'    => 'mysql',
                'host'      => env('DB_HOST', '127.0.0.1'),
                'port'      => env('DB_PORT', '3306'),
                'database'  => 'kps',
                'username'  => 'root',
                'password'  => null,
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
                'strict'    => true,
                'engine'    => null,
            ]);

            // Set Laravel to use the new connection
            Config::set('database.default', 'dynamic');

            // Clear and reconnect database connection
            DB::purge('dynamic');
            DB::reconnect('dynamic');
        }
        return $next($request);
    }
}
