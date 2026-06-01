<section class="card" style="max-width:520px; margin:0 auto;">
  <h1 style="margin-top:0">Admin Login</h1>
  <p style="margin-top:0; color:#475569;">
    Use your admin credentials to access product management.
  </p>

  <?php if (!empty($error)): ?>
    <div style="border:1px solid #fecaca; background:#fff1f2; color:#9f1239; border-radius:0.5rem; padding:0.7rem; margin:0 0 0.9rem;">
      <?= e((string)$error) ?>
    </div>
  <?php endif; ?>

  <form method="post" action="<?= e(url('/admin/login')) ?>" novalidate>
    <?= csrf_field() ?>
    <label for="email" style="display:block; font-weight:600; margin-bottom:0.35rem;">Email</label>
    <input
      id="email"
      name="email"
      type="email"
      required
      autocomplete="email"
      style="width:100%; border:1px solid #cbd5e1; border-radius:0.55rem; padding:0.55rem 0.65rem; margin-bottom:0.8rem;"
    >

    <label for="password" style="display:block; font-weight:600; margin-bottom:0.35rem;">Password</label>
    <input
      id="password"
      name="password"
      type="password"
      required
      autocomplete="current-password"
      style="width:100%; border:1px solid #cbd5e1; border-radius:0.55rem; padding:0.55rem 0.65rem; margin-bottom:1rem;"
    >

    <button
      type="submit"
      style="border:0; background:#1d4ed8; color:#fff; border-radius:0.55rem; padding:0.55rem 0.95rem; cursor:pointer; font-weight:600;"
    >
      Sign in
    </button>
  </form>
</section>
