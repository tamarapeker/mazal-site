<section class="card">
  <h1><?= e((string)$title) ?></h1>
  <p class="muted"><?= e((string)$message) ?></p>
  <a class="button button-secondary" href="<?= e(url('/admin/products')) ?>">Volver a productos</a>
</section>
