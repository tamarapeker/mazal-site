<?php
$listUrl = static function (array $changes = []) use ($filters): string {
    $query = array_filter(array_replace($filters, $changes), static fn($value): bool => $value !== '');
    return url('/admin/products') . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
};
$hasFilters = $filters['q'] !== '' || $filters['category'] !== '' || $filters['status'] !== '';
$sortLabels = ['code' => 'Código', 'name' => 'Nombre', 'category' => 'Categoría'];
?>
<div class="page-heading heading-with-action">
  <div>
    <p class="eyebrow">Catálogo</p>
    <h1>Productos</h1>
    <p class="muted">Consultá el catálogo y encontrá cada producto por su código, nombre o categoría.</p>
  </div>
  <a class="button" href="<?= e(url('/admin/products/create')) ?>">+ Nuevo producto</a>
</div>

<?php if (!empty($success)): ?>
  <div class="alert alert-success form-alert" role="status"><?= e((string)$success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert form-alert" role="alert"><?= e((string)$error) ?></div>
<?php endif; ?>

<section class="card filter-card" aria-label="Filtros de productos">
  <form method="get" action="<?= e(url('/admin/products')) ?>" class="product-filters" role="search">
    <input type="hidden" name="sort" value="<?= e($filters['sort']) ?>">
    <input type="hidden" name="direction" value="<?= e($filters['direction']) ?>">
    <div class="search-field">
      <label for="product-search">Buscar producto</label>
      <input id="product-search" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Código o nombre…">
    </div>
    <div>
      <label for="product-category">Categoría</label>
      <select id="product-category" name="category">
        <option value="">Todas las categorías</option>
        <?php foreach ($categories as $category): ?>
          <option value="<?= (int)$category['id'] ?>" <?= $filters['category'] === (string)$category['id'] ? 'selected' : '' ?>><?= e((string)$category['name']) ?><?= (int)$category['is_active'] === 1 ? '' : ' (inactiva)' ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label for="product-status">Estado del producto</label>
      <select id="product-status" name="status">
        <option value="">Todos los estados</option>
        <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Activos</option>
        <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactivos</option>
      </select>
    </div>
    <div class="filter-actions">
      <button class="button" type="submit">Filtrar</button>
      <?php if ($hasFilters): ?>
        <a class="text-link" href="<?= e(url('/admin/products')) ?>">Limpiar filtros</a>
      <?php endif; ?>
    </div>
  </form>
</section>

<section class="card product-list" aria-labelledby="product-list-heading">
  <div class="list-heading">
    <h2 id="product-list-heading"><?= $total ?> <?= $total === 1 ? 'producto' : 'productos' ?><?= $hasFilters ? ($total === 1 ? ' encontrado' : ' encontrados') : ' en el catálogo' ?></h2>
    <p class="muted" id="sort-help">Ordená haciendo clic en Código, Nombre o Categoría.</p>
  </div>
  <div class="table-scroll" role="region" aria-label="Tabla de productos" tabindex="0">
    <table class="products-table" aria-describedby="sort-help">
      <caption class="sr-only">Listado de productos. Orden actual: <?= e($sortLabels[$filters['sort']]) ?>, <?= $filters['direction'] === 'asc' ? 'ascendente' : 'descendente' ?>.</caption>
      <thead>
        <tr>
          <th scope="col" class="image-column">Imagen</th>
          <?php foreach ($sortLabels as $key => $label): ?>
            <?php
            $isSorted = $filters['sort'] === $key;
            $nextDirection = $isSorted && $filters['direction'] === 'asc' ? 'desc' : 'asc';
            ?>
            <th scope="col" <?= $isSorted ? 'aria-sort="' . ($filters['direction'] === 'asc' ? 'ascending' : 'descending') . '"' : '' ?>>
              <a class="sort-link <?= $isSorted ? 'is-sorted' : '' ?>" href="<?= e($listUrl(['sort' => $key, 'direction' => $nextDirection])) ?>" aria-label="Ordenar por <?= e($label) ?>, <?= $nextDirection === 'asc' ? 'ascendente' : 'descendente' ?>">
                <?= e($label) ?> <span aria-hidden="true"><?= $isSorted ? ($filters['direction'] === 'asc' ? '↑' : '↓') : '↕' ?></span>
              </a>
            </th>
          <?php endforeach; ?>
          <th scope="col">Venta mínima</th>
          <th scope="col">Cantidad por bulto</th>
          <th scope="col">Estado</th>
          <th scope="col">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $product): ?>
          <?php
          $image = product_image_by_filename((string)($product['image_filename'] ?? ''));
          $isActive = (int)$product['is_active'] === 1;
          $statusAction = $isActive ? 'deactivate' : 'activate';
          $statusLabel = $isActive ? 'Desactivar' : 'Activar';
          ?>
          <tr data-product-code="<?= e((string)$product['product_code']) ?>">
            <td>
              <div class="product-thumbnail">
                <span class="image-placeholder" <?= $image !== null ? 'hidden' : '' ?>>Sin<br>imagen</span>
                <?php if ($image !== null): ?>
                  <img src="<?= e($image) ?>" alt="" width="52" height="52" loading="lazy" onerror="this.hidden = true; this.previousElementSibling.hidden = false;">
                <?php endif; ?>
              </div>
            </td>
            <td class="product-code"><?= e((string)$product['product_code']) ?></td>
            <td class="product-name"><?= e((string)$product['name']) ?></td>
            <td>
              <?= e((string)$product['category_name']) ?>
              <?php if ((int)$product['category_is_active'] !== 1): ?><span class="category-note">Categoría inactiva</span><?php endif; ?>
            </td>
            <td><?= e(trim((string)$product['unit']) ?: '—') ?></td>
            <td><?= e(trim((string)$product['quantity_per_bulk']) ?: '—') ?></td>
            <td><span class="status-badge <?= (int)$product['is_active'] === 1 ? 'status-active' : 'status-inactive' ?>"><?= (int)$product['is_active'] === 1 ? 'Activo' : 'Inactivo' ?></span></td>
            <td class="product-actions">
              <div class="row-actions">
                <a class="button button-secondary" href="<?= e(url('/admin/products/' . (int)$product['id'] . '/edit')) ?>" aria-label="Editar <?= e((string)$product['product_code']) ?>">Editar</a>
                <form method="post" action="<?= e(url('/admin/products/' . (int)$product['id'] . '/' . $statusAction)) ?>" data-product-status-form data-status-action="<?= e($statusAction) ?>" data-product-code="<?= e((string)$product['product_code']) ?>" data-product-name="<?= e((string)$product['name']) ?>">
                  <?= csrf_field() ?>
                  <input type="hidden" name="return_query" value="<?= e(http_build_query(array_merge($filters, ['page' => $page]), '', '&', PHP_QUERY_RFC3986)) ?>">
                  <button class="button <?= $isActive ? 'button-danger' : 'button-activate' ?>" type="submit" aria-label="<?= e($statusLabel . ' ' . $product['product_code']) ?>" data-product-status-button><?= e($statusLabel) ?></button>
                </form>
              </div>
              <?php if ((int)$product['is_active'] === 1 && (int)$product['category_is_active'] === 1): ?>
                <a class="text-link product-detail" href="<?= e(url('/product/' . rawurlencode(strtolower((string)$product['product_code'])))) ?>" target="_blank" rel="noopener" aria-label="Ver ficha de <?= e((string)$product['name']) ?> (abre en otra pestaña)">Ver ficha <span aria-hidden="true">↗</span></a>
              <?php else: ?>
                <span class="muted">Sin ficha pública</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if ($products === []): ?>
          <tr>
            <td colspan="8" class="empty-state">
              <strong><?= $hasFilters ? 'No encontramos productos con esos filtros.' : 'Todavía no hay productos en el catálogo.' ?></strong>
              <p class="muted"><?= $hasFilters ? 'Probá con otra búsqueda o limpiá los filtros para ver todos los productos.' : 'Los productos que agregues aparecerán acá.' ?></p>
              <?php if ($hasFilters): ?><a class="text-link" href="<?= e(url('/admin/products')) ?>">Ver todos los productos</a><?php endif; ?>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="list-footer">
    <p class="muted">Mostrando <?= $firstResult ?>–<?= $lastResult ?> de <?= $total ?> <?= $total === 1 ? 'producto' : 'productos' ?></p>
    <?php if ($totalPages > 1): ?>
      <nav class="pagination" aria-label="Páginas de productos">
        <?php if ($page > 1): ?>
          <a class="page-link" href="<?= e($listUrl(['page' => $page - 1])) ?>" rel="prev">Anterior</a>
        <?php endif; ?>
        <?php
        $visiblePages = array_unique(array_merge([1], range(max(1, $page - 2), min($totalPages, $page + 2)), [$totalPages]));
        $previousPage = 0;
        ?>
        <?php foreach ($visiblePages as $number): ?>
          <?php if ($previousPage > 0 && $number > $previousPage + 1): ?><span class="page-gap" aria-hidden="true">…</span><?php endif; ?>
          <a class="page-link <?= $number === $page ? 'current-page' : '' ?>" href="<?= e($listUrl(['page' => $number])) ?>" aria-label="Página <?= $number ?>" <?= $number === $page ? 'aria-current="page"' : '' ?>><?= $number ?></a>
          <?php $previousPage = $number; ?>
        <?php endforeach; ?>
        <?php if ($page < $totalPages): ?>
          <a class="page-link" href="<?= e($listUrl(['page' => $page + 1])) ?>" rel="next">Siguiente</a>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  </div>
</section>
<dialog class="confirm-dialog" id="product-status-dialog" aria-labelledby="product-status-title" aria-describedby="product-status-description">
  <h2 id="product-status-title">Cambiar estado del producto</h2>
  <p class="dialog-product" id="product-status-label"></p>
  <p class="muted" id="product-status-description"></p>
  <div class="dialog-actions">
    <button class="button button-secondary" type="button" id="cancel-status-change" autofocus>Cancelar</button>
    <button class="button" type="button" id="confirm-status-change">Confirmar</button>
  </div>
</dialog>
<script src="<?= e(asset('js/admin-product-list.js')) ?>" defer></script>
