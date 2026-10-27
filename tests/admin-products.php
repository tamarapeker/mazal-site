<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/app.php';

function check_products(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$pdo = App\Core\Database::connection();
// Temporary tables shadow the real catalog on this connection only.
$pdo->exec('CREATE TEMPORARY TABLE categories (
    id INT PRIMARY KEY, name VARCHAR(120), is_active TINYINT NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$pdo->exec('CREATE TEMPORARY TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY, category_id INT, product_code VARCHAR(40),
    name VARCHAR(180), unit VARCHAR(50), quantity_per_bulk VARCHAR(100),
    image_filename VARCHAR(255), is_active TINYINT NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$pdo->exec("INSERT INTO categories VALUES
    (1, 'Bulonería', 1), (2, 'Ferretería', 1), (3, 'Jardinería', 0), (4, 'Sin productos', 1)");
$insert = $pdo->prepare('INSERT INTO products
    (category_id, product_code, name, unit, quantity_per_bulk, image_filename, is_active)
    VALUES (?, ?, ?, ?, ?, ?, ?)');
foreach ([
    [2, 'A01', 'Zócalo', 'unidad', '10 unidades', 'ANTPRO.PNG', 1],
    [1, 'B02', 'Abrazadera', 'unidad', '20 unidades', '', 1],
    [3, 'C03', 'Manguera', 'metro', '', '', 1],
    [2, 'D04', 'Martillo', 'unidad', '', '', 0],
    [2, 'E05', '<script>alert("x")</script>', '', '', '', 1],
    [2, 'F06', '100% puro', '', '', '', 1],
    [2, 'G07', 'medida_especial', '', '', '', 1],
    [2, 'H08', 'Seguro! reforzado', '', '', '', 1],
] as $product) {
    $insert->execute($product);
}
for ($number = 1; $number <= 55; $number++) {
    $insert->execute([2, sprintf('R%03d', $number), sprintf('Repuesto %02d', $number), 'unidad', '', '', 1]);
}

$_SESSION['admin_user'] = ['id' => 1, 'full_name' => 'Test Admin', 'email' => 'test@example.com', 'role' => 'editor'];
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['REQUEST_URI'] = '/admin/products';
$controller = new App\Controllers\Admin\ProductController();
$render = static function (array $query = []) use ($controller): string {
    $_GET = $query;
    ob_start();
    try {
        $controller->index();
        return (string)ob_get_contents();
    } finally {
        ob_end_clean();
    }
};
$codes = static function (string $html): array {
    preg_match_all('/<tr data-product-code="([^"]+)"/', $html, $matches);
    return $matches[1];
};
$links = static function (string $html, string $class): array {
    $document = new DOMDocument();
    @$document->loadHTML('<?xml encoding="UTF-8">' . $html);
    $xpath = new DOMXPath($document);
    $urls = [];
    foreach ($xpath->query('//a[contains(concat(" ", normalize-space(@class), " "), " ' . $class . ' ")]') as $anchor) {
        $urls[] = $anchor->getAttribute('href');
    }
    return $urls;
};

$default = $render();
check_products(count($codes($default)) === 25, 'The default page must contain 25 products.');
check_products(array_slice($codes($default), 0, 8) === ['A01', 'B02', 'C03', 'D04', 'E05', 'F06', 'G07', 'H08'], 'Default order must be ascending product code.');
check_products(str_contains($default, 'Mostrando 1–25 de 63 productos'), 'The total must include all products, including inactive records.');
check_products(str_contains($default, 'Categoría inactiva'), 'Inactive categories must remain visible and labeled.');
check_products(str_contains($default, '/assets/images/products/ANTPRO.png'), 'Images must use the existing filename normalization.');
check_products(str_contains($default, '&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;') && !str_contains($default, '<script>alert'), 'Product values must be HTML escaped.');

check_products($codes($render(['q' => 'zocalo'])) === ['A01'], 'Search must be case and accent insensitive under the catalog collation.');
check_products($codes($render(['q' => 'b02'])) === ['B02'], 'Search must find product codes.');
check_products($codes($render(['q' => '%'])) === ['F06'], 'A percent sign must be a literal search character.');
check_products($codes($render(['q' => '_'])) === ['G07'], 'An underscore must be a literal search character.');
check_products($codes($render(['q' => '!'])) === ['H08'], 'The LIKE escape character must remain searchable.');
check_products($codes($render(['q' => "' OR 1=1 --"])) === [], 'Search must be bound, not interpolated into SQL.');
check_products($codes($render(['category' => '1'])) === ['B02'], 'The category filter must apply.');
check_products($codes($render(['category' => '3'])) === ['C03'], 'Products in inactive categories must be accessible.');
check_products($codes($render(['status' => 'inactive'])) === ['D04'], 'Inactive products must be filterable.');
check_products(!in_array('D04', $codes($render(['status' => 'active'])), true), 'Active filtering must exclude inactive products.');
check_products($codes($render(['q' => 'mart', 'category' => '2', 'status' => 'inactive'])) === ['D04'], 'All three filters must combine.');
check_products($codes($render(['q' => 'mart', 'category' => '1', 'status' => 'inactive'])) === [], 'Filters must combine with AND.');

check_products($codes($render(['sort' => 'code', 'direction' => 'desc']))[0] === 'R055', 'Codes must sort descending.');
check_products(array_slice($codes($render(['sort' => 'name'])), 0, 3) === ['E05', 'F06', 'B02'], 'Names must sort ascending.');
check_products($codes($render(['sort' => 'name', 'direction' => 'desc']))[0] === 'A01', 'Names must sort descending.');
check_products($codes($render(['sort' => 'category']))[0] === 'B02', 'Categories must sort ascending.');
check_products($codes($render(['sort' => 'category', 'direction' => 'desc']))[0] === 'C03', 'Categories must sort descending.');

$query = ['q' => 'Repuesto', 'category' => '2', 'status' => 'active', 'sort' => 'name', 'direction' => 'desc'];
$first = $render($query);
$second = $render($query + ['page' => '2']);
$third = $render($query + ['page' => '3']);
$allCodes = array_merge($codes($first), $codes($second), $codes($third));
check_products(count($allCodes) === 55 && count(array_unique($allCodes)) === 55, 'Pagination must expose every matching product once.');
check_products($allCodes[0] === 'R055' && $allCodes[54] === 'R001', 'Ordering must apply to the full result before pagination.');
foreach ($links($second, 'page-link') as $link) {
    parse_str((string)parse_url($link, PHP_URL_QUERY), $parameters);
    foreach ($query as $key => $value) {
        check_products(($parameters[$key] ?? null) === $value, 'Pagination must preserve ' . $key . '.');
    }
}
foreach ($links($second, 'sort-link') as $link) {
    parse_str((string)parse_url($link, PHP_URL_QUERY), $parameters);
    check_products(!isset($parameters['page']), 'Sorting must reset the current page.');
    foreach (['q', 'category', 'status'] as $key) {
        check_products(($parameters[$key] ?? null) === $query[$key], 'Sorting must preserve ' . $key . '.');
    }
}
check_products(str_contains($second, 'Mostrando 26–50 de 55 productos'), 'The result range must follow the page.');
check_products($codes($render(['page' => '-5'])) === $codes($default), 'Negative pages must fall back to page one.');
check_products(count($codes($render(['page' => '999999999']))) === 13, 'Out-of-range pages must clamp to the last page.');
check_products($codes($render(['q' => [], 'category' => [], 'status' => [], 'sort' => [], 'direction' => [], 'page' => []])) === $codes($default), 'Array query parameters must be handled safely.');
check_products($codes($render(['sort' => 'p.name; DROP TABLE products', 'direction' => 'desc; --'])) === $codes($default), 'Sort columns and directions must use allowlists.');

$empty = $render(['category' => '4']);
check_products($codes($empty) === [] && str_contains($empty, 'No encontramos productos con esos filtros.'), 'Empty filter results must have a useful message.');
check_products(str_contains($empty, 'Mostrando 0–0 de 0 productos'), 'Empty filter results must have a zero result range.');

$_SERVER['SCRIPT_NAME'] = '/mazal-site/public/index.php';
$_SERVER['REQUEST_URI'] = '/mazal-site/public/admin/products';
$subdirectory = $render($query);
check_products(str_contains($subdirectory, 'action="/mazal-site/public/admin/products"'), 'Filter forms must support Apache subdirectories.');
foreach (array_merge($links($subdirectory, 'sort-link'), $links($subdirectory, 'page-link')) as $link) {
    check_products(str_starts_with($link, '/mazal-site/public/admin/products?'), 'Navigation must preserve the installation subdirectory.');
}

$pdo->exec('DELETE FROM products');
$pdo->exec('DELETE FROM categories');
$emptyCatalog = $render();
check_products($codes($emptyCatalog) === [] && str_contains($emptyCatalog, 'Todavía no hay productos en el catálogo.'), 'An empty catalog must render successfully.');

echo 'Admin product checks passed: search, literal wildcards, combined filters, six sort orders, pagination, escaping, invalid inputs, empty catalogs and Apache subdirectories.' . PHP_EOL;
