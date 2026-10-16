<?php
declare(strict_types=1);

return [
    'app' => [
        'name' => (string)env('APP_NAME', 'Mazal Catalog'),
        'env' => (string)env('APP_ENV', 'production'),
        'debug' => to_bool(env('APP_DEBUG', false), false),
        'url' => (string)env('APP_URL', 'http://localhost'),
        'timezone' => (string)env('APP_TIMEZONE', 'UTC'),
    ],
    'database' => [
        'host' => (string)env('DB_HOST', '127.0.0.1'),
        'port' => (int)env('DB_PORT', 3306),
        'name' => (string)env('DB_NAME', ''),
        'user' => (string)env('DB_USER', ''),
        'password' => (string)env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'host' => (string)env('MAIL_HOST', ''),
        'port' => (int)env('MAIL_PORT', 587),
        'username' => (string)env('MAIL_USERNAME', ''),
        'password' => (string)env('MAIL_PASSWORD', ''),
        'encryption' => (string)env('MAIL_ENCRYPTION', 'tls'),
        'from_address' => (string)env('MAIL_FROM_ADDRESS', 'info@mazalimportaciones.com.ar'),
        'from_name' => (string)env('MAIL_FROM_NAME', 'Mazal Importaciones'),
    ],
    'session' => [
        'name' => (string)env('SESSION_NAME', 'mazal_session'),
    ],
];
