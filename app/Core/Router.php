<?php
declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;

final class Router
{
    /**
     * @var array<string, array<int, array{pattern:string, handler:callable|array{0:object,1:string}}>>
     */
    private array $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'PATCH' => [],
        'DELETE' => [],
    ];

    public function get(string $pattern, callable|array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable|array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function add(string $method, string $pattern, callable|array $handler): void
    {
        $httpMethod = strtoupper($method);
        if (!isset($this->routes[$httpMethod])) {
            $this->routes[$httpMethod] = [];
        }

        $this->routes[$httpMethod][] = [
            'pattern' => $this->normalizePattern($pattern),
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $path): void
    {
        $httpMethod = strtoupper($method);
        $normalizedPath = $this->normalizePattern($path);

        $routes = $this->routes[$httpMethod] ?? [];
        foreach ($routes as $route) {
            $regex = $this->patternToRegex($route['pattern']);
            if (!preg_match($regex, $normalizedPath, $matches)) {
                continue;
            }

            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }

            $this->runHandler($route['handler'], $params);
            return;
        }

        http_response_code(404);
        View::render('errors/404', ['title' => 'Not Found'], 'main', 404);
    }

    private function runHandler(callable|array $handler, array $params): void
    {
        if (is_callable($handler)) {
            $handler($params);
            return;
        }

        if (is_array($handler) && count($handler) === 2 && is_object($handler[0]) && is_string($handler[1])) {
            [$controller, $method] = $handler;
            $controller->{$method}($params);
            return;
        }

        throw new InvalidArgumentException('Invalid route handler.');
    }

    private function normalizePattern(string $pattern): string
    {
        $pattern = '/' . ltrim(trim($pattern), '/');
        return $pattern === '/' ? $pattern : rtrim($pattern, '/');
    }

    private function patternToRegex(string $pattern): string
    {
        $escaped = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_-]*)\}#', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $escaped . '$#';
    }
}
