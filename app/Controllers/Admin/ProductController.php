<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\View;
use App\Services\ProductImageUpload;
use InvalidArgumentException;
use PDO;
use PDOException;
use Throwable;

final class ProductController
{
    public function index(): void
    {
        require_admin_auth();

        // Only these columns and directions can become part of the SQL query.
        $sortColumns = ['code' => 'p.product_code', 'name' => 'p.name', 'category' => 'c.name'];
        $queryString = static fn(string $key): string => is_string($_GET[$key] ?? null) ? trim($_GET[$key]) : '';
        $search = $queryString('q');
        $categoryId = $queryString('category');
        $status = $queryString('status');
        $sort = array_key_exists($queryString('sort'), $sortColumns) ? $queryString('sort') : 'code';
        $direction = $queryString('direction') === 'desc' ? 'desc' : 'asc';
        $page = filter_var($queryString('page'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
        $perPage = 25;

        $pdo = Database::connection();
        // Inactive categories and products must also be accessible in the backoffice.
        $categories = $pdo->query('SELECT id, name, is_active FROM categories ORDER BY name ASC')->fetchAll();
        if (!in_array($categoryId, array_map(static fn(array $category): string => (string)$category['id'], $categories), true)) {
            $categoryId = '';
        }
        if (!in_array($status, ['active', 'inactive'], true)) {
            $status = '';
        }

        $conditions = [];
        $parameters = [];
        if ($search !== '') {
            // Treat % and _ as literal search text, rather than LIKE wildcards.
            $pattern = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            $conditions[] = "(p.product_code LIKE :search_code ESCAPE '!' OR p.name LIKE :search_name ESCAPE '!')";
            $parameters['search_code'] = $pattern;
            $parameters['search_name'] = $pattern;
        }
        if ($categoryId !== '') {
            $conditions[] = 'p.category_id = :category_id';
            $parameters['category_id'] = (int)$categoryId;
        }
        if ($status !== '') {
            $conditions[] = 'p.is_active = :is_active';
            $parameters['is_active'] = $status === 'active' ? 1 : 0;
        }

        $from = ' FROM products p INNER JOIN categories c ON c.id = p.category_id';
        $where = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
        $count = $pdo->prepare('SELECT COUNT(*)' . $from . $where);
        $count->execute($parameters);
        $total = (int)$count->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $statement = $pdo->prepare(
            'SELECT p.id, p.product_code, p.name, p.unit, p.quantity_per_bulk, p.image_filename, p.is_active,
                    c.name AS category_name, c.is_active AS category_is_active'
            . $from . $where
            . ' ORDER BY ' . $sortColumns[$sort] . ' ' . strtoupper($direction)
            . ($sort === 'code' ? ', p.id ASC' : ', p.product_code ASC, p.id ASC')
            . ' LIMIT :limit OFFSET :offset',
        );
        foreach ($parameters as $key => $value) {
            $statement->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        View::render('admin/products/index', [
            'title' => 'Productos',
            'products' => $statement->fetchAll(),
            'categories' => $categories,
            'filters' => ['q' => $search, 'category' => $categoryId, 'status' => $status, 'sort' => $sort, 'direction' => $direction],
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'firstResult' => $total === 0 ? 0 : $offset + 1,
            'lastResult' => min($offset + $perPage, $total),
            'success' => pull_flash('product_success'),
            'error' => pull_flash('product_error'),
        ], 'admin');
    }

    public function create(): void
    {
        require_admin_auth();
        $this->renderForm();
    }

    public function store(): void
    {
        require_admin_auth();
        $this->save();
    }

    public function edit(array $params): void
    {
        require_admin_auth();
        $product = $this->findProduct($params);
        if ($product === null) {
            $this->productError('Producto no encontrado', 'El producto que buscás no existe.', 404);
            return;
        }
        $this->renderForm($this->productValues($product), [], 200, $product);
    }

    public function update(array $params): void
    {
        require_admin_auth();
        $product = $this->findProduct($params);
        if ($product === null) {
            $this->productError('Producto no encontrado', 'El producto que buscás no existe.', 404);
            return;
        }
        $this->save($product);
    }

    public function deactivate(array $params): void
    {
        $this->setStatus($params, 0);
    }

    public function activate(array $params): void
    {
        $this->setStatus($params, 1);
    }

    private function setStatus(array $params, int $active): void
    {
        require_admin_auth();
        $action = $active === 1 ? 'activar' : 'desactivar';
        $token = $_POST['_token'] ?? null;
        if (!verify_csrf(is_string($token) ? $token : null)) {
            $this->productError('No pudimos ' . $action . ' el producto', 'La sesión del formulario venció. Volvé a la tabla e intentá nuevamente.', 403);
            return;
        }
        $product = $this->findProduct($params);
        if ($product === null) {
            $this->productError('Producto no encontrado', 'El producto que buscás no existe.', 404);
            return;
        }
        try {
            // Each endpoint requests a specific state, making repeated submissions safe.
            $statement = Database::connection()->prepare('UPDATE products SET is_active = :active WHERE id = :id');
            $statement->execute(['active' => $active, 'id' => $product['id']]);
            flash('product_success', 'El producto ' . $product['product_code'] . ($active === 1 ? ' se activó.' : ' se desactivó.'));
        } catch (Throwable $exception) {
            error_log('[products] Product status change failed: ' . $exception->getMessage());
            flash('product_error', 'No pudimos ' . $action . ' el producto. Intentá nuevamente.');
        }
        redirect($this->returnToList());
    }

    private function save(?array $product = null): void
    {
        $editing = $product !== null;
        $original = $editing ? $this->productValues($product) : [];

        // PHP discards both POST and FILES when the entire request exceeds this limit.
        $postLimit = trim((string)ini_get('post_max_size'));
        $postBytes = (float)$postLimit * (1024 ** (['k' => 1, 'm' => 2, 'g' => 3][strtolower(substr($postLimit, -1))] ?? 0));
        if ($postBytes > 0 && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > $postBytes) {
            $this->renderForm($original, ['form' => 'El envío supera el tamaño permitido. Elegí una imagen más liviana y volvé a completar el formulario.'], 413, $product);
            return;
        }

        $values = [];
        $errors = [];
        foreach (['product_code', 'name', 'category_id', 'description', 'unit', 'quantity_per_bulk', 'is_active'] as $field) {
            // Codes and status cannot be changed through the edit form.
            $raw = $editing && in_array($field, ['product_code', 'is_active'], true)
                ? $original[$field] : ($_POST[$field] ?? ($original[$field] ?? ''));
            $values[$field] = is_string($raw) ? trim($raw) : '';
            if (!is_string($raw)) {
                $errors[$field] = 'Ingresá un valor válido.';
            }
        }
        if (!$editing) {
            $values['product_code'] = strtoupper($values['product_code']);
        }
        $values['description'] = str_replace(["\r\n", "\r"], "\n", $values['description']);
        $rawSizes = $_POST['sizes'] ?? ($original['sizes'] ?? []);
        $values['sizes'] = [];
        if (!is_array($rawSizes) || count($rawSizes) > 50) {
            $errors['sizes'] = 'Podés agregar hasta 50 medidas, una por campo.';
        }
        foreach (is_array($rawSizes) ? array_slice($rawSizes, 0, 50) : [] as $size) {
            if (!is_string($size)) {
                $errors['sizes'] = 'Cada medida debe tener hasta 80 caracteres válidos.';
                continue;
            }
            if (!$this->validText(trim($size), 80)) {
                $errors['sizes'] = 'Cada medida debe tener hasta 80 caracteres válidos.';
            }
            if (trim($size) !== '') {
                $values['sizes'][] = trim($size);
            }
        }
        $values['sizes'] = array_values(array_unique($values['sizes']));
        $removeImage = $_POST['remove_image'] ?? '0';
        if (!is_string($removeImage) || !in_array($removeImage, ['0', '1'], true)) {
            $errors['image'] = 'Seleccioná una opción válida para la imagen.';
        }
        $values['remove_image'] = $removeImage === '1' ? '1' : '0';

        $token = $_POST['_token'] ?? null;
        if (!verify_csrf(is_string($token) ? $token : null)) {
            $this->renderForm($values, ['form' => 'La sesión del formulario venció. Revisá los datos y volvé a guardar.'], 403, $product);
            return;
        }
        if (!$editing && preg_match('/\A[A-Z0-9][A-Z0-9_-]{0,39}\z/', $values['product_code']) !== 1) {
            $errors['product_code'] = 'Ingresá un código de hasta 40 caracteres: letras, números, guiones o guiones bajos.';
        }
        foreach (['name' => ['el nombre', 180, true], 'unit' => ['la venta mínima', 50, false], 'quantity_per_bulk' => ['la cantidad por bulto', 100, false]] as $field => [$label, $limit, $required]) {
            if (($required && $values[$field] === '') || !$this->validText($values[$field], $limit)) {
                $errors[$field] = 'Completá ' . $label . ' con hasta ' . $limit . ' caracteres válidos.';
            }
        }
        if (preg_match('/\A[^\x00-\x08\x0B\x0C\x0E-\x1F\x7F]{0,5000}\z/u', $values['description']) !== 1) {
            $errors['description'] = 'La descripción debe tener hasta 5000 caracteres válidos.';
        }
        if (!$editing && !in_array($values['is_active'], ['0', '1'], true)) {
            $errors['is_active'] = 'Seleccioná el estado del producto.';
        }

        $pdo = Database::connection();
        $category = $pdo->prepare('SELECT id FROM categories WHERE id = :id');
        $category->execute(['id' => $values['category_id']]);
        if (!ctype_digit($values['category_id']) || !$category->fetchColumn()) {
            $errors['category_id'] = 'Seleccioná una categoría válida.';
        }
        if (!$editing) {
            $existing = $pdo->prepare('SELECT id FROM products WHERE product_code = :code LIMIT 1');
            $existing->execute(['code' => $values['product_code']]);
            if ($existing->fetchColumn()) {
                $errors['product_code'] = 'Ya existe un producto con ese código.';
            }
        }

        $uploader = new ProductImageUpload();
        try {
            $image = $uploader->validate($_FILES['image'] ?? null);
        } catch (InvalidArgumentException $exception) {
            $errors['image'] = $exception->getMessage();
        } catch (Throwable $exception) {
            error_log('[products] Image validation failed: ' . $exception->getMessage());
            $errors['image'] = 'No pudimos procesar la imagen. Intentá nuevamente.';
        }
        if ($errors !== []) {
            $this->renderForm($values, $errors, 422, $product);
            return;
        }

        $filename = null;
        $newFilename = null;
        $oldFilename = null;
        try {
            $pdo->beginTransaction();
            if ($editing) {
                // Preserve the latest stored image, even if another editor changed it meanwhile.
                $current = $pdo->prepare('SELECT image_filename FROM products WHERE id = :id FOR UPDATE');
                $current->execute(['id' => $product['id']]);
                $currentProduct = $current->fetch();
                if (!$currentProduct) {
                    throw new \RuntimeException('The product no longer exists.');
                }
                $oldFilename = $currentProduct['image_filename'];
                $filename = $values['remove_image'] === '1' ? null : $oldFilename;
            }
            if ($image !== null) {
                $newFilename = $uploader->save($image);
                $filename = $newFilename;
            }
            $parameters = [
                'category' => (int)$values['category_id'],
                'name' => $values['name'],
                'description' => $values['description'] !== '' ? $values['description'] : null,
                'sizes' => $values['sizes'] !== [] ? json_encode($values['sizes'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) : null,
                'unit' => $values['unit'],
                'bulk' => $values['quantity_per_bulk'] !== '' ? $values['quantity_per_bulk'] : null,
                'image' => $filename,
                'active' => (int)$values['is_active'],
            ];
            if ($editing) {
                unset($parameters['active']);
                $parameters['id'] = $product['id'];
                $statement = $pdo->prepare('UPDATE products SET category_id = :category, name = :name,
                    description = :description, sizes = :sizes, unit = :unit, quantity_per_bulk = :bulk,
                    image_filename = :image WHERE id = :id');
            } else {
                $parameters['code'] = $values['product_code'];
                $parameters['slug'] = $this->productSlug($values['name'], $values['product_code']);
                $statement = $pdo->prepare('INSERT INTO products
                    (category_id, product_code, name, slug, description, sizes, unit, quantity_per_bulk, image_filename, is_active)
                    VALUES (:category, :code, :name, :slug, :description, :sizes, :unit, :bulk, :image, :active)');
            }
            $statement->execute($parameters);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($newFilename !== null) {
                $uploader->remove($newFilename);
            }
            if ($exception instanceof PDOException && (int)($exception->errorInfo[1] ?? 0) === 1062) {
                $errors['product_code'] = 'Ya existe un producto con ese código o identificador. Revisá el código.';
            } else {
                error_log('[products] Product save failed: ' . $exception->getMessage());
                $errors['form'] = 'No pudimos guardar el producto. Los datos se conservaron; intentá nuevamente.';
            }
            $this->renderForm($values, $errors, 422, $product);
            return;
        }

        if ($oldFilename !== null && $oldFilename !== $filename) {
            try {
                $references = $pdo->prepare('SELECT COUNT(*) FROM products WHERE image_filename = :filename');
                $references->execute(['filename' => $oldFilename]);
                if ((int)$references->fetchColumn() === 0) {
                    $uploader->remove((string)$oldFilename);
                }
            } catch (Throwable $exception) {
                error_log('[products] Old image cleanup failed: ' . $exception->getMessage());
            }
        }
        flash('product_success', 'El producto ' . $values['product_code'] . ($editing ? ' se actualizó correctamente.' : ' se creó correctamente.'));
        redirect('/admin/products?q=' . rawurlencode($values['product_code']));
    }

    private function renderForm(array $values = [], array $errors = [], int $status = 200, ?array $product = null): void
    {
        View::render('admin/products/form', [
            'title' => $product !== null ? 'Editar producto' : 'Nuevo producto',
            'editing' => $product !== null,
            'formAction' => $product !== null ? '/admin/products/' . $product['id'] . '/update' : '/admin/products',
            'existingImage' => $product !== null ? product_image_by_filename($product['image_filename']) : null,
            'values' => array_replace(['product_code' => '', 'name' => '', 'category_id' => '', 'description' => '', 'unit' => '', 'quantity_per_bulk' => '', 'sizes' => [], 'is_active' => '1', 'remove_image' => '0'], $values),
            'errors' => $errors,
            'categories' => Database::connection()->query('SELECT id, name, is_active FROM categories ORDER BY name ASC')->fetchAll(),
            'hadImage' => isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE,
        ], 'admin', $status);
    }

    private function findProduct(array $params): ?array
    {
        $id = filter_var($params['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            return null;
        }
        $statement = Database::connection()->prepare('SELECT id, product_code, name, category_id, description,
            sizes, unit, quantity_per_bulk, image_filename, is_active FROM products WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    private function productValues(array $product): array
    {
        $sizes = json_decode((string)($product['sizes'] ?? ''), true);
        $values = [];
        foreach (['product_code', 'name', 'category_id', 'description', 'unit', 'quantity_per_bulk', 'is_active'] as $field) {
            $values[$field] = (string)($product[$field] ?? '');
        }
        $values['sizes'] = is_array($sizes) ? array_values(array_filter($sizes, 'is_string')) : [];
        return $values;
    }

    private function productError(string $title, string $message, int $status): void
    {
        View::render('admin/products/error', ['title' => $title, 'message' => $message], 'admin', $status);
    }

    private function returnToList(): string
    {
        $query = [];
        $raw = $_POST['return_query'] ?? '';
        if (is_string($raw)) {
            parse_str($raw, $query);
        }
        // Only a query string for our own list can be used as the return destination.
        $query = array_filter(array_intersect_key($query, array_flip(['q', 'category', 'status', 'sort', 'direction', 'page'])), 'is_string');
        return '/admin/products' . ($query !== [] ? '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986) : '');
    }

    private function validText(string $value, int $limit): bool
    {
        return preg_match('/\A[^\x00-\x1F\x7F]{0,' . $limit . '}\z/u', $value) === 1;
    }

    private function productSlug(string $name, string $code): string
    {
        // iconv transliteration varies by host, so normalize Spanish accents explicitly.
        $name = strtr($name, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N']);
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        $nameSlug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($ascii !== false ? $ascii : $name)) ?? '', '-');
        return rtrim(substr($nameSlug !== '' ? $nameSlug : 'producto', 0, 164), '-')
            . '-' . strtolower($code) . '-' . bin2hex(random_bytes(4));
    }
}
