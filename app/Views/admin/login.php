<section class="card login-card">
  <p class="eyebrow">Backoffice Mazal</p>
  <h1>Iniciar sesión</h1>
  <p class="muted">Ingresá con tu cuenta de administrador para acceder al catálogo.</p>

  <?php if (!empty($error)): ?>
    <div class="alert" role="alert"><?= e((string)$error) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= e(url('/admin/login')) ?>">
    <?= csrf_field() ?>
    <div>
      <label for="email">Email</label>
      <input id="email" name="email" type="email" required autocomplete="username">
    </div>
    <div>
      <label for="password">Contraseña</label>
      <input id="password" name="password" type="password" required autocomplete="current-password">
    </div>
    <button class="button" type="submit">Ingresar</button>
  </form>
</section>
