<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Excluded Hosts
    |--------------------------------------------------------------------------
    |
    | Hosts listed here are NOT treated as school tenant subdomains. Requests
    | to these hosts are served by the default (base) database connection,
    | which is useful for separate apps hosted under the same parent domain
    | (e.g. papermaker.idlbridge.com points to a different directory/app).
    |
    | Comma-separated in the env, exact hostnames only (no scheme/port).
    |
    */

    'excluded_hosts' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('TENANCY_EXCLUDED_HOSTS', 'papermaker.idlbridge.com'))
    ))),

];
