<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SubdomainDatabaseSwitcher
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];

        // Cache the database details for 10 minutes
        $details = Cache::remember("db_details_{$subdomain}", 600, function () use ($subdomain) {
            return DB::table('databases')->where('subdomain', $subdomain)->first();
        });

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

            Config::set('database.default', 'dynamic');
            DB::purge('dynamic');
            DB::reconnect('dynamic');
            return $next($request);
        }
        return 'Database not found';
    }
}
