<?php
declare(strict_types=1);

use App\Controllers\PublicController;
use App\Core\Database;

require dirname(__DIR__) . '/bootstrap/app.php';

function check_featured(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$pdo = Database::connection();
// These connection-local tables shadow the real catalog and disappear on exit.
$pdo->exec('CREATE TEMPORARY TABLE categories (
    id INT PRIMARY KEY, slug VARCHAR(160), is_active TINYINT NOT NULL
)');
$pdo->exec('CREATE TEMPORARY TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY, category_id INT,
    product_code VARCHAR(40), name VARCHAR(180), unit VARCHAR(50),
    quantity_per_bulk VARCHAR(100), image_filename VARCHAR(255),
    is_active TINYINT NOT NULL
)');

$categoryInsert = $pdo->prepare('INSERT INTO categories VALUES (?, ?, ?)');
$productInsert = $pdo->prepare('INSERT INTO products
    (category_id, product_code, name, unit, quantity_per_bulk, image_filename, is_active)
    VALUES (?, ?, ?, ?, ?, ?, ?)');
$eligibleProducts = [];
for ($category = 1; $category <= 9; $category++) {
    $slug = 'category-' . $category;
    $categoryInsert->execute([$category, $slug, $category === 7 ? 0 : 1]);
    if ($category <= 7) {
        // Different catalog sizes should still allow one product per category.
        for ($product = 1; $product <= $category + 1; $product++) {
            $code = 'product-' . $category . '-' . $product;
            $productInsert->execute([$category, $code, $code, 'unidad', '10 unidades', $code . '.png', 1]);
            if ($category !== 7) {
                $eligibleProducts[$code] = $slug;
            }
        }
    }
    if ($category !== 8) {
        $productInsert->execute([$category, 'inactive-' . $category, 'Inactive product', 'unidad', '', '', 0]);
    }
}

$controller = new PublicController();
$fetch = new ReflectionMethod(PublicController::class, 'fetchFeaturedProducts');
$fetch->setAccessible(true);
$categorySelections = [];
$productSelections = [];
for ($visit = 0; $visit < 40; $visit++) {
    $featured = $fetch->invoke($controller);
    $slugs = array_column($featured, 'category_slug');
    $codes = array_column($featured, 'product_code');
    check_featured(count($featured) === 4, 'Expected four featured products.');
    check_featured(count(array_unique($slugs)) === 4, 'Categories must not repeat within a selection.');
    check_featured(count(array_unique($codes)) === 4, 'Products must not repeat within a selection.');
    foreach ($featured as $product) {
        check_featured(($eligibleProducts[$product['product_code']] ?? null) === $product['category_slug'],
            'Featured products must belong to an active category and be active themselves.');
        foreach (['name', 'unit', 'quantity_per_bulk', 'image_filename'] as $field) {
            check_featured(isset($product[$field]), 'Missing home card field: ' . $field);
        }
    }
    sort($slugs);
    sort($codes);
    $categorySelections[implode(',', $slugs)] = true;
    $productSelections[implode(',', $codes)] = true;
}
check_featured(count($categorySelections) > 1, 'Categories should change across repeated visits.');
check_featured(count($productSelections) > 1, 'Products should change across repeated visits.');
check_featured(count($fetch->invoke($controller, 2)) === 2, 'Custom limits should be respected.');
check_featured($fetch->invoke($controller, 0) === [], 'A zero limit should return no products.');
check_featured($fetch->invoke($controller, -1) === [], 'A negative limit should return no products.');
$all = $fetch->invoke($controller, 20);
check_featured(count($all) === 6 && count(array_unique(array_column($all, 'category_slug'))) === 6,
    'An oversized limit should return only eligible categories, once each.');

$pdo->exec('UPDATE categories SET is_active = 0 WHERE id BETWEEN 3 AND 6');
check_featured(count($fetch->invoke($controller)) === 2,
    'With fewer than four eligible categories, return the available categories without duplication.');
$pdo->exec('UPDATE products SET is_active = 0');
check_featured($fetch->invoke($controller) === [], 'Inactive products must not be featured.');
$pdo->exec('DELETE FROM products');
$pdo->exec('DELETE FROM categories');
check_featured($fetch->invoke($controller) === [], 'An empty catalog should return no products.');

echo 'Featured product checks passed: four distinct categories, random categories and products, active records only, limits and sparse/empty catalogs.' . PHP_EOL;
