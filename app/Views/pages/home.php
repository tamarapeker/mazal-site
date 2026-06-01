<section class="relative h-[36vh] min-h-[178px] overflow-hidden border-b border-emerald-950/10 bg-cover bg-center sm:h-[56vh] sm:min-h-[340px]" style="background-image:linear-gradient(rgba(2,38,1,.2), rgba(2,38,1,.2)), url('<?= e(asset('images/banners/home-hero.png')) ?>');">
</section>

<section class="site-shell py-10">
  <?php if (empty($categories)): ?>
    <p class="mt-5 rounded-xl border border-dashed border-zinc-400 bg-white p-5 text-sm text-zinc-500">
      No se encontraron categorias. Por favor, vuelva mas tarde.
    </p>
  <?php else: ?>
    <div class="mt-7 grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-7">
      <?php foreach ($categories as $category): ?>
        <a href="<?= e(url('/category/' . (string)$category['slug'])) ?>" class="group text-center">
          <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full border border-mazal-forest/40 bg-white shadow-sm transition group-hover:scale-105 group-hover:border-mazal-green">
            <img
              src="<?= e(category_image_by_filename((string)($category['image_filename'] ?? ''), (string)$category['slug'])) ?>"
              alt="<?= e((string)$category['name']) ?>"
              class="h-full w-full rounded-full object-cover">
          </div>
          <p class="mx-auto mt-3 inline-block rounded-md bg-mazal-lime px-3 py-1 text-xs font-extrabold tracking-wide text-mazal-forest transition group-hover:bg-mazal-orange">
            <?= e((string)$category['name']) ?>
          </p>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<section class="site-shell pb-10">
  <h2 class="section-chip">Productos destacados</h2>

  <?php if (empty($featuredProducts)): ?>
    <p class="mt-5 rounded-xl border border-dashed border-zinc-400 bg-white p-5 text-sm text-zinc-500">
      No hay productos destacados.
    </p>
  <?php else: ?>
    <div class="mt-6 grid gap-4 px-3 sm:grid-cols-2 sm:px-0 lg:grid-cols-4">
      <?php foreach ($featuredProducts as $product): ?>
        <?php $featuredImage = product_image_by_filename((string)($product['image_filename'] ?? '')); ?>
        <?php $unitValue = trim((string)($product['unit'] ?? '')); ?>
        <?php $unitDisplay = ($unitValue !== '' && strtolower($unitValue) !== 'unit') ? $unitValue : '-'; ?>
        <?php $bulkValue = trim((string)($product['quantity_per_bulk'] ?? '')); ?>
        <article class="catalog-card mx-auto w-full max-w-sm overflow-hidden sm:max-w-none">
          <?php if ($featuredImage !== null): ?>
            <img
              src="<?= e($featuredImage) ?>"
              alt="<?= e((string)$product['name']) ?>"
              class="aspect-[4/3] w-full object-cover">
          <?php else: ?>
            <div class="aspect-[4/3] bg-gradient-to-br from-mazal-lime/25 to-mazal-green/30"></div>
          <?php endif; ?>
          <div class="p-4">
            <h3 class="text-lg font-bold text-mazal-forest"><?= e((string)$product['name']) ?></h3>
            <p class="mt-2 text-sm text-zinc-700">Venta minima: <?= e($unitDisplay) ?></p>
            <p class="text-sm text-zinc-600">Bulto por <?= e($bulkValue !== '' ? $bulkValue : '-') ?></p>
            <a href="<?= e(url('/product/' . strtolower((string)$product['product_code']))) ?>" class="pill-button-orange mt-4">Ver detalles</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<section class="relative overflow-hidden border-y border-emerald-950/10 bg-cover bg-center py-16" style="background-image:linear-gradient(rgba(2,38,1,.45), rgba(2,38,1,.45)), url('<?= e(asset('images/banners/cta-warehouse.png')) ?>');">
  <div class="site-shell text-center text-white">
    <h3 class="font-display text-3xl font-extrabold uppercase tracking-wider">Queres hacer tu pedido?</h3>
    <a href="<?= e(url('/contact')) ?>" class="pill-button mt-6">Contactanos</a>
  </div>
</section>
