<section class="relative h-[24vh] min-h-[150px] overflow-hidden border-b border-emerald-950/10 bg-cover bg-center sm:h-[38vh] sm:min-h-[220px]" style="background-image:linear-gradient(rgba(2,38,1,.35), rgba(2,38,1,.35)), url('<?= e(asset('images/banners/inner-hero.png')) ?>');">
  <div class="site-shell flex h-full items-center">
    <h1 class="font-display text-4xl font-extrabold uppercase tracking-widest text-white sm:text-5xl">Catálogo</h1>
  </div>
</section>

<section class="site-shell py-10">
  <h2 class="section-chip">Categorías</h2>

  <?php if (empty($categories)): ?>
    <p class="mt-5 rounded-xl border border-dashed border-zinc-400 bg-white p-5 text-sm text-zinc-500">
      No se encontraron categorías. Por favor, vuelva más tarde.
    </p>
  <?php else: ?>
    <div class="mt-7 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($categories as $category): ?>
        <article class="catalog-card overflow-hidden">
          <div class="flex items-center gap-4 border-b border-emerald-950/10 bg-white p-4">
            <img
              src="<?= e(category_image_by_filename((string)($category['image_filename'] ?? ''), (string)$category['slug'])) ?>"
              alt="<?= e((string)$category['name']) ?>"
              class="h-16 w-16 rounded-full border border-mazal-forest/25 object-cover">
            <div>
              <h3 class="text-2xl font-extrabold uppercase text-mazal-forest"><?= e((string)$category['name']) ?></h3>
              <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">
                <?= (int)$category['products_count'] ?> productos
              </p>
            </div>
          </div>
          <div class="p-4">
            <a href="<?= e(url('/category/' . (string)$category['slug'])) ?>" class="pill-button-orange mt-4">Ver Productos</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
