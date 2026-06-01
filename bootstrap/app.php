<?php
declare(strict_types=1);

use App\Controllers\Admin\AuthController;
use App\Controllers\PublicController;
use App\Core\Router;

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/Support/helpers.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = base_path('app/' . str_replace('\\', '/', $relativeClass) . '.php');

    if (is_file($file)) {
        require $file;
    }
});

load_environment(base_path('.env'));
$config = require base_path('config/app.php');
set_app_config($config);

date_default_timezone_set((string)config('app.timezone', 'UTC'));
start_session_if_needed();

$router = new Router();

$publicController = new PublicController();
$authController = new AuthController();

require base_path('routes/web.php');

return $router;

function load_environment(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (!str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($key === '') {
            continue;
        }

        $value = trim($value, " \t\n\r\0\x0B\"'");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv($key . '=' . $value);
    }
}

function start_session_if_needed(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name((string)config('session.name', 'mazal_session'));
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}
