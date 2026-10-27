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
$categoryInsert->execute([1, 'jardineria', 1]);
$categoryInsert->execute([2, 'ferreteria', 1]);
$expectedCodes = ['JARC', 'JARMA', 'JARPI6E', 'JARUM'];
// Insert in reverse order, with all featured products in the same category.
foreach (array_reverse($expectedCodes) as $code) {
    $productInsert->execute([1, $code, $code, 'unidad', '10 unidades', $code . '.png', 1]);
}
$productInsert->execute([1, 'OTHER-GARDEN', 'Other garden product', 'unidad', '', '', 1]);
$productInsert->execute([2, 'OTHER-HARDWARE', 'Other hardware product', 'unidad', '', '', 1]);

$controller = new PublicController();
$fetch = new ReflectionMethod(PublicController::class, 'fetchFeaturedProducts');
$fetch->setAccessible(true);
for ($visit = 0; $visit < 10; $visit++) {
    $featured = $fetch->invoke($controller);
    check_featured(array_column($featured, 'product_code') === $expectedCodes,
        'Every visit must show exactly JARC, JARMA, JARPI6E and JARUM in that order.');
    foreach ($featured as $product) {
        check_featured($product['category_slug'] === 'jardineria',
            'The fixed selection must allow products from the same category.');
        foreach (['name', 'quantity_per_bulk', 'image_filename'] as $field) {
            check_featured(isset($product[$field]), 'Missing home card field: ' . $field);
        }
    }
}

$pdo->exec("UPDATE products SET is_active = 0 WHERE product_code = 'JARMA'");
check_featured(array_column($fetch->invoke($controller), 'product_code') === ['JARC', 'JARPI6E', 'JARUM'],
    'An inactive featured product must be omitted without selecting a replacement.');
$pdo->exec("UPDATE products SET is_active = 1 WHERE product_code = 'JARMA'");
$pdo->exec("DELETE FROM products WHERE product_code = 'JARPI6E'");
check_featured(array_column($fetch->invoke($controller), 'product_code') === ['JARC', 'JARMA', 'JARUM'],
    'A missing featured product must not be replaced by another code.');
$pdo->exec('UPDATE categories SET is_active = 0 WHERE id = 1');
check_featured($fetch->invoke($controller) === [], 'Products in inactive categories must not be featured.');
$pdo->exec('DELETE FROM products');
check_featured($fetch->invoke($controller) === [], 'An empty catalog should return no products.');

echo 'Featured product checks passed: fixed codes and order across visits, shared categories allowed, no replacements, active records only and empty catalogs.' . PHP_EOL;
