<section class="relative h-[22vh] min-h-[140px] overflow-hidden border-b border-emerald-950/10 bg-cover bg-center sm:h-[34vh] sm:min-h-[200px]" style="background-image:linear-gradient(rgba(2,38,1,.35), rgba(2,38,1,.35)), url('<?= e(asset('images/banners/inner-hero.png')) ?>');">
  <div class="site-shell flex h-full items-center justify-between gap-3">
    <h1 class="font-display text-4xl font-extrabold uppercase tracking-widest text-white sm:text-5xl"><?= e((string)$category['name']) ?></h1>
    <a href="<?= e(url('/categories')) ?>" class="hidden rounded-full border border-white/50 bg-white/15 px-4 py-2 text-xs font-extrabold uppercase tracking-wide text-white backdrop-blur transition hover:bg-white/25 sm:inline-flex">Volver a categorias</a>
  </div>
</section>

<section class="site-shell py-10">
  <a href="<?= e(url('/categories')) ?>" class="inline-flex rounded-full border border-emerald-950/15 bg-white px-4 py-2 text-xs font-bold uppercase tracking-wide text-zinc-600 hover:text-mazal-green sm:hidden">Volver a categorias</a>

  <?php if (empty($products)): ?>
    <p class="mt-5 rounded-xl border border-dashed border-zinc-400 bg-white p-5 text-sm text-zinc-500">
      No se encontraron productos en esta categoria. Por favor, vuelva mas tarde.
    </p>
  <?php else: ?>
    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($products as $product): ?>
        <?php $productImage = product_image_by_filename((string)($product['image_filename'] ?? '')); ?>
        <?php $unitValue = trim((string)($product['unit'] ?? '')); ?>
        <?php $unitDisplay = ($unitValue !== '' && strtolower($unitValue) !== 'unit') ? $unitValue : '-'; ?>
        <article class="catalog-card overflow-hidden">
          <?php if ($productImage !== null): ?>
            <img
              src="<?= e($productImage) ?>"
              alt="<?= e((string)$product['name']) ?>"
              class="aspect-[4/3] w-full object-cover">
          <?php else: ?>
            <div class="aspect-[4/3] bg-gradient-to-br from-mazal-lime/20 to-mazal-green/35"></div>
          <?php endif; ?>
          <div class="p-5">
            <p class="text-xs font-extrabold uppercase tracking-wider text-zinc-400"><?= e((string)$product['product_code']) ?></p>
            <h2 class="mt-2 text-2xl font-extrabold uppercase text-mazal-forest"><?= e((string)$product['name']) ?></h2>
            <p class="mt-3 text-sm text-zinc-700">Venta minima: <?= e($unitDisplay) ?></p>
            <a href="<?= e(url('/product/' . strtolower((string)$product['product_code']))) ?>" class="pill-button-orange mt-4">Detalle del producto</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
