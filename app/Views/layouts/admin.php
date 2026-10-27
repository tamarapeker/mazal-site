<!doctype html>
<html lang="es-AR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e(($title ?? '') . ' | Mazal Backoffice') ?></title>
  <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body>
  <header class="topbar">
    <div class="container topbar-row">
      <a class="admin-brand" href="<?= e(url('/admin')) ?>">
        <img src="<?= e(asset('images/brand/logo.png')) ?>" alt="Mazal" width="106" height="40">
        <span>Backoffice</span>
      </a>
      <?php if (is_admin_authenticated()): ?>
        <nav class="admin-nav" aria-label="Navegación del backoffice">
          <a href="<?= e(url('/admin')) ?>" <?= is_current_path('/admin') ? 'aria-current="page"' : '' ?>>Resumen</a>
          <a href="<?= e(url('/admin/products')) ?>" <?= str_starts_with(request_path(), '/admin/products') ? 'aria-current="page"' : '' ?>>Productos</a>
        </nav>
        <form method="post" action="<?= e(url('/admin/logout')) ?>" class="logout-form">
          <?= csrf_field() ?>
          <button class="button button-secondary" type="submit">Cerrar sesión</button>
        </form>
      <?php endif; ?>
    </div>
  </header>
  <main class="container">
    <?= $content ?>
  </main>
</body>
</html>
