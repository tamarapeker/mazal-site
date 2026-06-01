<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'main', int $statusCode = 200): void
    {
        http_response_code($statusCode);

        $viewPath = base_path('app/Views/' . str_replace('.', '/', $view) . '.php');
        if (!is_file($viewPath)) {
            throw new RuntimeException('View not found: ' . $viewPath);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = (string)ob_get_clean();

        $layoutPath = base_path('app/Views/layouts/' . $layout . '.php');
        if (!is_file($layoutPath)) {
            echo $content;
            return;
        }

        require $layoutPath;
    }
}
