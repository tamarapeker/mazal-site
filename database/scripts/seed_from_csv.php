#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Seeds categories and products from a semicolon-delimited CSV file.
 *
 * Example:
 * php database/scripts/seed_from_csv.php ^
 *   --csv="C:\path\products.csv" ^
 *   --database=hardware_importer_catalog ^
 *   --username=root ^
 *   --password=""
 */

main();

function main(): void
{
    $options = getopt(
        '',
        [
            'csv:',
            'host::',
            'port::',
            'database::',
            'username::',
            'password::',
            'no-truncate',
            'dry-run',
            'help',
        ],
    );

    if (isset($options['help'])) {
        printUsage();
        return;
    }

    $csvPath = $options['csv'] ?? null;
    if ($csvPath === null) {
        fwrite(STDERR, "Error: --csv is required.\n\n");
        printUsage();
        exit(1);
    }

    $host = (string)($options['host'] ?? getenv('DB_HOST') ?: '127.0.0.1');
    $port = (int)($options['port'] ?? getenv('DB_PORT') ?: 3306);
    $database = (string)($options['database'] ?? getenv('DB_NAME') ?: 'hardware_importer_catalog');
    $username = (string)($options['username'] ?? getenv('DB_USER') ?: 'root');
    $password = (string)($options['password'] ?? getenv('DB_PASS') ?: '');
    $truncateBeforeSeed = !isset($options['no-truncate']);
    $dryRun = isset($options['dry-run']);

    $rows = readCsvRows((string)$csvPath);

    if ($dryRun) {
        $summary = summarizeRows($rows);
        fwrite(STDOUT, "Dry run summary\n");
        fwrite(STDOUT, "---------------\n");
        fwrite(STDOUT, "CSV path: " . $csvPath . PHP_EOL);
        fwrite(STDOUT, "Valid products: " . $summary['valid_products'] . PHP_EOL);
        fwrite(STDOUT, "Skipped empty rows: " . $summary['skipped_empty_rows'] . PHP_EOL);
        fwrite(STDOUT, "Skipped rows without code: " . $summary['skipped_missing_code'] . PHP_EOL);
        fwrite(STDOUT, "Categories detected: " . count($summary['categories']) . PHP_EOL);
        foreach ($summary['categories'] as $category) {
            fwrite(STDOUT, " - {$category}" . PHP_EOL);
        }
        return;
    }

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $database);
    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    );

    $insertedCategories = 0;
    $insertedProducts = 0;
    $skippedEmptyRows = 0;
    $skippedMissingCode = 0;
    $categoryIdBySlug = [];
    $productSlugRegistry = [];

    $insertCategorySql = <<<SQL
INSERT INTO categories (name, slug, image_filename, display_order)
VALUES (:name, :slug, :image_filename, :display_order)
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  image_filename = VALUES(image_filename),
  id = LAST_INSERT_ID(id)
SQL;

    $insertProductSql = <<<SQL
INSERT INTO products (
  category_id,
  product_code,
  name,
  slug,
  description,
  sizes,
  unit,
  quantity_per_bulk,
  image_filename
) VALUES (
  :category_id,
  :product_code,
  :name,
  :slug,
  :description,
  :sizes,
  :unit,
  :quantity_per_bulk,
  :image_filename
)
ON DUPLICATE KEY UPDATE
  id = LAST_INSERT_ID(id),
  category_id = VALUES(category_id),
  name = VALUES(name),
  slug = VALUES(slug),
  description = VALUES(description),
  sizes = VALUES(sizes),
  unit = VALUES(unit),
  quantity_per_bulk = VALUES(quantity_per_bulk),
  image_filename = VALUES(image_filename)
SQL;

    $categoryStmt = $pdo->prepare($insertCategorySql);
    $productStmt = $pdo->prepare($insertProductSql);

    $pdo->beginTransaction();
    try {
        if ($truncateBeforeSeed) {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
            $pdo->exec('TRUNCATE TABLE products');
            $pdo->exec('TRUNCATE TABLE categories');
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        }

        foreach ($rows as $row) {
            if (isRowEmpty($row)) {
                $skippedEmptyRows++;
                continue;
            }

            $rawCategoryName = $row['category_name'];
            $categorySlug = slugify((string)$rawCategoryName);
            if ($categorySlug === '') {
                $skippedEmptyRows++;
                continue;
            }

            if (!isset($categoryIdBySlug[$categorySlug])) {
                $displayOrder = count($categoryIdBySlug) + 1;
                $categoryStmt->execute([
                    ':name' => toDisplayCase((string)$rawCategoryName),
                    ':slug' => $categorySlug,
                    ':image_filename' => categoryImageFilename((string)$rawCategoryName),
                    ':display_order' => $displayOrder,
                ]);
                $categoryIdBySlug[$categorySlug] = (int)$pdo->lastInsertId();
                $insertedCategories++;
            }

            $productCode = strtoupper(trim((string)$row['product_code']));
            if ($productCode === '') {
                $skippedMissingCode++;
                continue;
            }

            $name = trim((string)($row['name'] ?? ''));
            $baseSlug = slugify($name !== '' ? $name : $productCode);
            $baseSlug .= '-' . strtolower($productCode);
            $productSlug = uniqueSlug($baseSlug, $productSlugRegistry);

            $sizesJson = encodeSizesAsJson($row['measures_raw']);
            $unit = trim((string)($row['unit'] ?? ''));
            $quantityPerBulk = $row['quantity_per_bulk'];

            $productStmt->execute([
                ':category_id' => $categoryIdBySlug[$categorySlug],
                ':product_code' => $productCode,
                ':name' => $name,
                ':slug' => $productSlug,
                ':description' => $row['description'],
                ':sizes' => $sizesJson,
                ':unit' => $unit,
                ':quantity_per_bulk' => $quantityPerBulk,
                ':image_filename' => $row['image_filename'],
            ]);
            $insertedProducts++;
        }

        $pdo->commit();
    } catch (Throwable $error) {
        $pdo->rollBack();
        throw $error;
    }

    fwrite(STDOUT, "Seed completed successfully.\n");
    fwrite(STDOUT, "Categories processed: {$insertedCategories}\n");
    fwrite(STDOUT, "Products processed: {$insertedProducts}\n");
    fwrite(STDOUT, "Skipped empty rows: {$skippedEmptyRows}\n");
    fwrite(STDOUT, "Skipped rows without product code: {$skippedMissingCode}\n");
}

function printUsage(): void
{
    $usage = <<<TXT
Usage:
  php database/scripts/seed_from_csv.php --csv=PATH [options]

Required:
  --csv=PATH                Absolute or relative path to the source CSV.

Optional:
  --host=HOST               Database host (default: 127.0.0.1)
  --port=PORT               Database port (default: 3306)
  --database=NAME           Database name (default: hardware_importer_catalog)
  --username=USER           Database username (default: root)
  --password=PASS           Database password (default: empty)
  --no-truncate             Keep existing category/product records.
  --dry-run                 Parse CSV and print summary without writing to DB.
  --help                    Print this help message.

TXT;

    fwrite(STDOUT, $usage);
}

/**
 * @return list<array{
 *   category_name:?string,
 *   product_code:?string,
 *   name:?string,
 *   description:?string,
 *   measures_raw:?string,
 *   unit:?string,
 *   quantity_per_bulk:?string,
 *   image_filename:?string
 * }>
 */
function readCsvRows(string $csvPath): array
{
    if (!is_file($csvPath)) {
        throw new RuntimeException("CSV file not found: {$csvPath}");
    }

    $handle = fopen($csvPath, 'rb');
    if ($handle === false) {
        throw new RuntimeException("Cannot read CSV file: {$csvPath}");
    }

    $header = fgetcsv($handle, 0, ';');
    if ($header === false) {
        fclose($handle);
        throw new RuntimeException('CSV file is empty.');
    }

    $normalizedHeader = [];
    foreach ($header as $columnName) {
        $normalizedHeader[] = canonicalizeHeaderKey(normalizeHeader((string)$columnName));
    }

    $headerIndex = array_flip($normalizedHeader);
    $requiredHeaders = [
        'categoria',
        'id',
        'nombre',
        'descripcion',
        'medidas',
        'unidad_de_medida_de_venta',
        'cantidad_por_bulto_cerrado',
        'imagen',
    ];

    foreach ($requiredHeaders as $requiredHeader) {
        if (!array_key_exists($requiredHeader, $headerIndex)) {
            fclose($handle);
            throw new RuntimeException("Missing expected CSV header: {$requiredHeader}");
        }
    }

    $rows = [];
    while (($rawRow = fgetcsv($handle, 0, ';')) !== false) {
        $rows[] = [
            'category_name' => sanitizeCell($rawRow[(int)$headerIndex['categoria']] ?? null),
            'product_code' => sanitizeCell($rawRow[(int)$headerIndex['id']] ?? null),
            'name' => sanitizeCell($rawRow[(int)$headerIndex['nombre']] ?? null),
            'description' => sanitizeCell($rawRow[(int)$headerIndex['descripcion']] ?? null),
            'measures_raw' => sanitizeCell($rawRow[(int)$headerIndex['medidas']] ?? null),
            'unit' => sanitizeCell($rawRow[(int)$headerIndex['unidad_de_medida_de_venta']] ?? null),
            'quantity_per_bulk' => sanitizeCell($rawRow[(int)$headerIndex['cantidad_por_bulto_cerrado']] ?? null),
            'image_filename' => sanitizeCell($rawRow[(int)$headerIndex['imagen']] ?? null),
        ];
    }

    fclose($handle);

    return $rows;
}

function normalizeHeader(string $header): string
{
    $header = removeUtf8Bom($header);
    $header = sanitizeToUtf8($header);
    $ascii = transliterateToAscii($header);
    $ascii = strtolower($ascii);
    $ascii = preg_replace('/[^a-z0-9]+/', '_', $ascii) ?? '';
    return trim($ascii, '_');
}

function canonicalizeHeaderKey(string $key): string
{
    $compact = str_replace('_', '', strtolower(trim($key)));

    $map = [
        'categoria' => 'categoria',
        'categora' => 'categoria',
        'category' => 'categoria',
        'id' => 'id',
        'nombre' => 'nombre',
        'name' => 'nombre',
        'descripcion' => 'descripcion',
        'descripcin' => 'descripcion',
        'description' => 'descripcion',
        'medidas' => 'medidas',
        'measures' => 'medidas',
        'unitaddemedidadeventa' => 'unidad_de_medida_de_venta',
        'unidaddemedidadeventa' => 'unidad_de_medida_de_venta',
        'salesunit' => 'unidad_de_medida_de_venta',
        'cantidadporbultocerrado' => 'cantidad_por_bulto_cerrado',
        'quantityperbulk' => 'cantidad_por_bulto_cerrado',
        'imagen' => 'imagen',
        'image' => 'imagen',
    ];

    return $map[$compact] ?? $key;
}

function sanitizeCell(?string $value): ?string
{
    if ($value === null) {
        return null;
    }

    $utf8 = sanitizeToUtf8($value);
    return $utf8 === '' ? null : $utf8;
}

function sanitizeToUtf8(string $value): string
{
    $value = str_replace("\u{00A0}", ' ', $value);
    $value = trim($value);

    if ($value === '') {
        return '';
    }

    if (!preg_match('//u', $value)) {
        $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $value);
        if ($converted !== false) {
            $value = $converted;
        }
    }

    return trim($value);
}

function removeUtf8Bom(string $value): string
{
    return (string)preg_replace('/^\xEF\xBB\xBF/', '', $value);
}

function transliterateToAscii(string $value): string
{
    $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    if ($converted !== false) {
        return $converted;
    }

    return (string)preg_replace('/[^\x20-\x7E]/', '', $value);
}

function slugify(string $value): string
{
    $value = sanitizeToUtf8($value);
    $ascii = transliterateToAscii($value);
    $ascii = strtolower($ascii);
    $ascii = preg_replace('/[^a-z0-9]+/', '-', $ascii) ?? '';
    return trim($ascii, '-');
}

function uniqueSlug(string $baseSlug, array &$slugRegistry): string
{
    $slug = $baseSlug;
    $counter = 2;
    while (isset($slugRegistry[$slug])) {
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }

    $slugRegistry[$slug] = true;
    return $slug;
}

function categoryImageFilename(string $categoryName): string
{
    $slug = slugify($categoryName);
    $slug = str_replace('-', '', $slug);

    if ($slug === '') {
        return 'CATEGORY.png';
    }

    return strtoupper($slug) . '.png';
}

function toDisplayCase(string $value): string
{
    $value = sanitizeToUtf8($value);
    if ($value === '') {
        return $value;
    }

    if (function_exists('mb_convert_case')) {
        return mb_convert_case(mb_strtolower($value, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    return ucwords(strtolower($value));
}

function encodeSizesAsJson(?string $measuresRaw): ?string
{
    if ($measuresRaw === null || trim($measuresRaw) === '') {
        return null;
    }

    $sizes = splitMeasureValues($measuresRaw);
    if ($sizes === []) {
        return null;
    }

    $json = json_encode($sizes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new RuntimeException('Could not encode sizes JSON.');
    }

    return $json;
}

/**
 * Splits the CSV "Medidas" value into a JSON-ready array of strings.
 *
 * @return list<string>
 */
function splitMeasureValues(string $measuresRaw): array
{
    $parts = explode(';', $measuresRaw);
    $values = [];

    for ($index = 0, $total = count($parts); $index < $total; $index++) {
        $value = trim($parts[$index]);

        // Handle malformed decimal values such as "2;5 mm x 47 cm" in source CSV.
        $nextValue = $parts[$index + 1] ?? null;
        if ($nextValue !== null) {
            $nextValue = trim((string)$nextValue);
            if (
                preg_match('/^[0-9]+$/', $value) === 1
                && preg_match('/^[0-9]+\\s*[a-zA-Z]/', $nextValue) === 1
            ) {
                $value = $value . ';' . $nextValue;
                $index++;
            }
        }

        if ($value !== '') {
            $values[] = $value;
        }
    }

    return $values;
}

function isRowEmpty(array $row): bool
{
    foreach ($row as $value) {
        if ($value !== null && trim((string)$value) !== '') {
            return false;
        }
    }
    return true;
}

function summarizeRows(array $rows): array
{
    $validProducts = 0;
    $skippedEmptyRows = 0;
    $skippedMissingCode = 0;
    $categories = [];

    foreach ($rows as $row) {
        if (isRowEmpty($row)) {
            $skippedEmptyRows++;
            continue;
        }

        $categoryName = (string)($row['category_name'] ?? '');
        if ($categoryName !== '') {
            $categories[slugify($categoryName)] = toDisplayCase($categoryName);
        }

        $productCode = trim((string)($row['product_code'] ?? ''));
        if ($productCode === '') {
            $skippedMissingCode++;
            continue;
        }

        $validProducts++;
    }

    return [
        'valid_products' => $validProducts,
        'skipped_empty_rows' => $skippedEmptyRows,
        'skipped_missing_code' => $skippedMissingCode,
        'categories' => array_values($categories),
    ];
}
