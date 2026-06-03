<?php
declare(strict_types=1);

$projectRoot = resolveProjectRoot();

if (PHP_SAPI === 'cli-server') {
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $filePath = __DIR__ . DIRECTORY_SEPARATOR . ltrim((string)$uriPath, '/');

    if ((string)$uriPath !== '/' && is_file($filePath)) {
        return false;
    }
}

/** @var \App\Core\Router $router */
$router = require $projectRoot . '/bootstrap/app.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$path = request_path();

$router->dispatch($method, $path);

function resolveProjectRoot(): string
{
    $candidates = [];

    $envBasePath = getenv('APP_BASE_PATH');
    if (is_string($envBasePath) && trim($envBasePath) !== '') {
        $candidates[] = rtrim($envBasePath, DIRECTORY_SEPARATOR);
    }

    $candidates[] = dirname(__DIR__);
    $candidates[] = __DIR__ . DIRECTORY_SEPARATOR . 'mazal-site';
    $candidates[] = __DIR__ . DIRECTORY_SEPARATOR . 'app-root';
    $candidates[] = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'mazal-site';
    $candidates[] = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app-root';

    foreach ($candidates as $candidate) {
        if (is_file($candidate . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php')) {
            return $candidate;
        }
    }

    throw new RuntimeException('Could not resolve project root for bootstrap/app.php');
}
