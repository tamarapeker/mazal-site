<div class="page-heading">
  <p class="eyebrow">Backoffice</p>
  <h1>Resumen del catálogo</h1>
  <?php if (!empty($admin)): ?>
    <p class="muted">Sesión de <?= e((string)$admin['full_name']) ?> · <?= e((string)$admin['email']) ?></p>
  <?php endif; ?>
</div>

<div class="stats-grid">
  <article class="card stat-card">
    <p>Categorías</p>
    <strong><?= (int)$stats['categories'] ?></strong>
  </article>
  <article class="card stat-card">
    <p>Productos</p>
    <strong><?= (int)$stats['products'] ?></strong>
  </article>
  <article class="card stat-card">
    <p>Productos activos</p>
    <strong><?= (int)$stats['active_products'] ?></strong>
  </article>
</div>

<section class="card catalog-entry">
  <div>
    <h2>Productos del catálogo</h2>
    <p class="muted">Buscá, filtrá y ordená todos los productos desde un solo lugar.</p>
  </div>
  <a class="button" href="<?= e(url('/admin/products')) ?>">Ver productos <span aria-hidden="true">→</span></a>
</section>
