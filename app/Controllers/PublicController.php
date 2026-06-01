<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\View;
use PDO;
use Throwable;

final class PublicController
{
    public function home(): void
    {
        $categories = $this->fetchCategorySummary();
        $featuredProducts = $this->fetchFeaturedProducts();

        View::render('pages/home', [
            'title' => 'Home',
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
        ]);
    }

    public function about(): void
    {
        View::render('pages/about', ['title' => 'About']);
    }

    public function contact(): void
    {
        View::render('pages/contact', ['title' => 'Contact']);
    }

    public function howToBuy(): void
    {
        View::render('pages/how-to-buy', ['title' => 'How to Buy']);
    }

    public function categories(): void
    {
        $categories = $this->fetchCategorySummary();
        View::render('pages/categories', [
            'title' => 'Categories',
            'categories' => $categories,
        ]);
    }

    public function categoryShow(array $params): void
    {
        $slug = (string)($params['slug'] ?? '');
        if ($slug === '') {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Category Not Found'], 'main', 404);
            return;
        }

        $pdo = Database::connection();

        $categoryStmt = $pdo->prepare('SELECT id, name, slug FROM categories WHERE slug = :slug AND is_active = 1 LIMIT 1');
        $categoryStmt->execute(['slug' => $slug]);
        $category = $categoryStmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Category Not Found'], 'main', 404);
            return;
        }

        $productsStmt = $pdo->prepare(
            'SELECT product_code, name, slug, unit, image_filename
             FROM products
             WHERE category_id = :category_id AND is_active = 1
             ORDER BY name ASC',
        );
        $productsStmt->execute(['category_id' => $category['id']]);
        $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);

        View::render('pages/category-show', [
            'title' => $category['name'],
            'category' => $category,
            'products' => $products,
        ]);
    }

    public function productShow(array $params): void
    {
        $code = strtoupper((string)($params['code'] ?? ''));
        if ($code === '') {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Product Not Found'], 'main', 404);
            return;
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT
                p.product_code,
                p.name,
                p.slug,
                p.description,
                p.sizes,
                p.unit,
                p.quantity_per_bulk,
                p.image_filename,
                c.name AS category_name,
                c.slug AS category_slug
             FROM products p
             INNER JOIN categories c ON c.id = p.category_id
             WHERE p.product_code = :product_code
               AND p.is_active = 1
               AND c.is_active = 1
             LIMIT 1',
        );
        $stmt->execute(['product_code' => $code]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Product Not Found'], 'main', 404);
            return;
        }

        $sizes = null;
        if (!empty($product['sizes'])) {
            $decoded = json_decode((string)$product['sizes'], true);
            if (is_array($decoded)) {
                $sizes = $decoded;
            }
        }

        View::render('pages/product-show', [
            'title' => $product['name'],
            'product' => $product,
            'sizes' => $sizes,
        ]);
    }

    private function fetchCategorySummary(): array
    {
        try {
            $stmt = Database::connection()->query(
                'SELECT
                    c.id,
                    c.name,
                    c.slug,
                    c.image_filename,
                    c.display_order,
                    COUNT(p.id) AS products_count
                 FROM categories c
                 LEFT JOIN products p
                    ON p.category_id = c.id
                   AND p.is_active = 1
                 WHERE c.is_active = 1
                 GROUP BY c.id, c.name, c.slug, c.image_filename, c.display_order
                 ORDER BY c.display_order ASC, c.name ASC',
            );

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable) {
            try {
                $stmt = Database::connection()->query(
                    'SELECT
                        c.id,
                        c.name,
                        c.slug,
                        NULL AS image_filename,
                        c.display_order,
                        COUNT(p.id) AS products_count
                     FROM categories c
                     LEFT JOIN products p
                        ON p.category_id = c.id
                       AND p.is_active = 1
                     WHERE c.is_active = 1
                     GROUP BY c.id, c.name, c.slug, c.display_order
                     ORDER BY c.display_order ASC, c.name ASC',
                );

                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Throwable) {
                return [];
            }
        }
    }

    private function fetchFeaturedProducts(int $limit = 4): array
    {
        try {
            $stmt = Database::connection()->prepare(
                'SELECT
                    p.product_code,
                    p.name,
                    p.unit,
                    p.quantity_per_bulk,
                    p.image_filename,
                    c.slug AS category_slug
                 FROM products p
                 INNER JOIN categories c ON c.id = p.category_id
                 WHERE p.is_active = 1
                   AND c.is_active = 1
                 ORDER BY p.id ASC
                 LIMIT :limit',
            );
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable) {
            return [];
        }
    }
}
