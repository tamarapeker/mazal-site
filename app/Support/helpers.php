<?php
declare(strict_types=1);

if (!function_exists('set_app_config')) {
    function set_app_config(array $config): void
    {
        $GLOBALS['app_config'] = $config;
    }
}

if (!function_exists('config')) {
    function config(?string $key = null, mixed $default = null): mixed
    {
        $config = $GLOBALS['app_config'] ?? [];
        if ($key === null) {
            return $config;
        }

        $segments = explode('.', $key);
        $value = $config;
        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }

        $value = getenv($key);
        if ($value === false) {
            return $default;
        }

        return $value;
    }
}

if (!function_exists('to_bool')) {
    function to_bool(mixed $value, bool $default = false): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return $parsed ?? $default;
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        if ($path === '') {
            return $base;
        }

        return $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        $public = base_path('public');
        if ($path === '') {
            return $public;
        }

        return $public . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('/assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('app_base_url_path')) {
    function app_base_url_path(): string
    {
        $scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
        $dir = str_replace('\\', '/', dirname($scriptName));

        if ($dir === '/' || $dir === '\\' || $dir === '.') {
            return '';
        }

        return rtrim($dir, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = '/'): string
    {
        $normalizedPath = '/' . ltrim($path, '/');
        $base = app_base_url_path();

        if ($base === '') {
            return $normalizedPath;
        }

        return $base . $normalizedPath;
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('request_path')) {
    function request_path(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = is_string($uri) ? $uri : '/';
        $path = '/' . ltrim($path, '/');

        $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $scriptName = rtrim($scriptName, '/');
        if ($scriptName !== '' && $scriptName !== '/' && str_starts_with($path, $scriptName)) {
            $path = substr($path, strlen($scriptName));
            $path = $path === '' ? '/' : $path;
        }

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }
}

if (!function_exists('is_current_path')) {
    function is_current_path(string $path): bool
    {
        return request_path() === $path;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        if (preg_match('#^https?://#i', $path) === 1) {
            header('Location: ' . $path);
            exit;
        }

        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('category_image_by_filename')) {
    function category_image_by_filename(?string $filename, string $fallbackSlug = ''): string
    {
        $name = trim((string)$filename);
        if ($name !== '') {
            $normalized = str_replace('\\', '/', $name);
            $safeFilename = basename($normalized);
            if ($safeFilename !== '') {
                return asset('images/categories/' . rawurlencode($safeFilename));
            }
        }

        return category_image_by_slug($fallbackSlug);
    }
}

if (!function_exists('category_image_by_slug')) {
    function category_image_by_slug(string $categorySlug): string
    {
        $slug = strtolower(trim($categorySlug));
        $slug = preg_replace('/[^a-z0-9]+/', '', $slug) ?? '';

        if ($slug === '') {
            return asset('images/categories/FERRETERIA.png');
        }

        return asset('images/categories/' . strtoupper($slug) . '.png');
    }
}

if (!function_exists('product_image_by_filename')) {
    function product_image_by_filename(?string $filename): ?string
    {
        $name = trim((string)$filename);
        if ($name === '') {
            return null;
        }

        $normalized = str_replace('\\', '/', $name);
        $safeFilename = basename($normalized);
        if ($safeFilename === '') {
            return null;
        }

        return asset('images/products/' . rawurlencode($safeFilename));
    }
}

if (!function_exists('flash')) {
    function flash(string $key, mixed $value): void
    {
        $_SESSION['flash'][$key] = $value;
    }
}

if (!function_exists('pull_flash')) {
    function pull_flash(string $key, mixed $default = null): mixed
    {
        if (!isset($_SESSION['flash'][$key])) {
            return $default;
        }

        $value = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $value;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return (string)$_SESSION['_csrf_token'];
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf(?string $token): bool
    {
        $sessionToken = $_SESSION['_csrf_token'] ?? '';
        return is_string($token)
            && is_string($sessionToken)
            && $sessionToken !== ''
            && hash_equals($sessionToken, $token);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('is_admin_authenticated')) {
    function is_admin_authenticated(): bool
    {
        return isset($_SESSION['admin_user']['id']);
    }
}

if (!function_exists('admin_user')) {
    function admin_user(): ?array
    {
        if (!is_admin_authenticated()) {
            return null;
        }

        return $_SESSION['admin_user'];
    }
}

if (!function_exists('require_admin_auth')) {
    function require_admin_auth(): void
    {
        if (is_admin_authenticated()) {
            return;
        }

        flash('auth_error', 'Please sign in to continue.');
        redirect('/admin/login');
    }
}
