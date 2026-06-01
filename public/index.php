<?php
declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $filePath = __DIR__ . DIRECTORY_SEPARATOR . ltrim((string)$uriPath, '/');

    if ((string)$uriPath !== '/' && is_file($filePath)) {
        return false;
    }
}

/** @var \App\Core\Router $router */
$router = require dirname(__DIR__) . '/bootstrap/app.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$path = request_path();

$router->dispatch($method, $path);
