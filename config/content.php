<?php

return [
    // Super Admin
    'super_admin' => env('APP_SUPER_ADMIN') ?: 1,

    // Admin Credentials
    'admin_name' => env('ADMIN_NAME', 'admin'),
    'admin_email' => env('ADMIN_EMAIL', 'admin@laravel-vue-admin.trumanwl.com'),
    'admin_password' => env('ADMIN_PASSWORD', '123456'),

    // Default Avatar
    'default_avatar' => env('DEFAULT_AVATAR', '/images/avatar.png'),

    // Color Theme
    'color_theme' => 'gray-theme',

    // Meta
    'meta' => [
        'keywords' => 'School Managment System, IDLSchool',
        'description' => 'A software to facilitate the school owner.'
    ],

    // Google Analytics
    'google' => [
        'id'   => env('GOOGLE_ANALYTICS_ID', 'Google-Analytics-ID'),
        'open' => env('GOOGLE_OPEN') ?: false
    ],

    // Footer
    'footer' => [
        'github' => [
            'open' => true,
            'url'  => 'https://github.com/trumanwong',
        ],
        'meta' => '© Laravel Vue Admin 2022. Powered By Truman',
    ],
];