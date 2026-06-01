<section class="card">
  <h1 style="margin-top:0">Dashboard</h1>
  <?php if (!empty($admin)): ?>
    <p style="margin-top:0; color:#475569;">
      Signed in as <strong><?= e((string)$admin['full_name']) ?></strong>
      (<?= e((string)$admin['email']) ?>)
    </p>
  <?php endif; ?>

  <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:0.8rem;">
    <article style="border:1px solid #dbe1e8; border-radius:0.65rem; padding:0.8rem;">
      <p style="margin:0; color:#475569;">Categories</p>
      <p style="margin:0.4rem 0 0; font-size:1.3rem; font-weight:700;"><?= (int)$stats['categories'] ?></p>
    </article>
    <article style="border:1px solid #dbe1e8; border-radius:0.65rem; padding:0.8rem;">
      <p style="margin:0; color:#475569;">Products</p>
      <p style="margin:0.4rem 0 0; font-size:1.3rem; font-weight:700;"><?= (int)$stats['products'] ?></p>
    </article>
    <article style="border:1px solid #dbe1e8; border-radius:0.65rem; padding:0.8rem;">
      <p style="margin:0; color:#475569;">Active products</p>
      <p style="margin:0.4rem 0 0; font-size:1.3rem; font-weight:700;"><?= (int)$stats['active_products'] ?></p>
    </article>
  </div>

  <p style="margin:1rem 0 0; color:#64748b;">
    Next step: implement product CRUD screens under this admin area.
  </p>
</section>
