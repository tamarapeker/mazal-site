<?php
$productImage = product_image_by_filename((string)($product['image_filename'] ?? ''));
$unitValue = trim((string)($product['unit'] ?? ''));
$unitDisplay = ($unitValue !== '' && strtolower($unitValue) !== 'unit') ? $unitValue : '-';
$bulkValue = trim((string)($product['quantity_per_bulk'] ?? ''));
$descriptionValue = trim((string)($product['description'] ?? ''));
$sizesList = [];

if (is_array($sizes)) {
  foreach ($sizes as $size) {
    $cleanSize = trim((string)$size);
    if ($cleanSize !== '') {
      $sizesList[] = $cleanSize;
    }
  }
}
?>

<section class="relative h-[22vh] min-h-[140px] overflow-hidden border-b border-emerald-950/10 bg-cover bg-center sm:h-[34vh] sm:min-h-[200px]" style="background-image:linear-gradient(rgba(2,38,1,.35), rgba(2,38,1,.35)), url('<?= e(asset('images/banners/inner-hero.png')) ?>');">
  <div class="site-shell flex h-full items-center">
    <div>
      <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-emerald-100">Detalle de producto</p>
      <h1 class="mt-2 max-w-4xl font-display text-4xl font-extrabold uppercase tracking-widest text-white sm:text-5xl"><?= e((string)$product['name']) ?></h1>
    </div>
  </div>
</section>

<section class="site-shell py-10">
  <a href="<?= e(url('/category/' . (string)$product['category_slug'])) ?>" class="inline-flex rounded-full border border-emerald-950/15 bg-white px-4 py-2 text-xs font-bold uppercase tracking-wide text-zinc-600 hover:text-mazal-green">
    Volver a <?= e((string)$product['category_name']) ?>
  </a>

  <div class="mt-6 grid gap-6 lg:grid-cols-[1.1fr_1.9fr]">
    <article class="catalog-card overflow-hidden">
      <?php if ($productImage !== null): ?>
        <img
          src="<?= e($productImage) ?>"
          alt="<?= e((string)$product['name']) ?>"
          class="aspect-[4/3] w-full object-cover">
      <?php else: ?>
        <div class="aspect-[4/3] bg-gradient-to-br from-mazal-lime/20 to-mazal-green/35"></div>
        <div class="border-t border-emerald-950/10 p-4 text-xs font-semibold uppercase tracking-wider text-zinc-500">
          Imagen no disponible
        </div>
      <?php endif; ?>
    </article>

    <article class="catalog-card p-6">
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <p class="text-xs font-extrabold uppercase tracking-widest text-zinc-400">Codigo</p>
          <p class="mt-1 text-lg font-bold text-zinc-800"><?= e((string)$product['product_code']) ?></p>
        </div>
        <div>
          <p class="text-xs font-extrabold uppercase tracking-widest text-zinc-400">Venta minima</p>
          <p class="mt-1 text-lg font-bold text-zinc-800"><?= e($unitDisplay) ?></p>
        </div>
      </div>

      <div class="mt-5 rounded-lg border border-emerald-950/15 bg-mazal-gray p-4">
        <p class="text-xs font-extrabold uppercase tracking-widest text-zinc-500">Bulto por</p>
        <p class="mt-1 text-base font-bold text-zinc-800"><?= e($bulkValue !== '' ? $bulkValue : '-') ?></p>
      </div>

      <div class="mt-5">
        <h2 class="text-2xl font-extrabold uppercase text-mazal-forest">Descripcion</h2>
        <p class="mt-2 whitespace-pre-line text-sm leading-7 text-zinc-700 sm:text-base">
          <?= e($descriptionValue !== '' ? $descriptionValue : '-') ?>
        </p>
      </div>

      <div class="mt-6">
        <h2 class="text-2xl font-extrabold uppercase text-mazal-forest">Medidas</h2>
        <?php if ($sizesList === []): ?>
          <p class="mt-3 text-sm font-semibold text-zinc-700">-</p>
        <?php else: ?>
          <div class="mt-3 flex flex-wrap gap-2">
            <?php foreach ($sizesList as $size): ?>
              <span class="rounded-md border border-emerald-950/20 bg-white px-3 py-1 text-xs font-bold uppercase tracking-wide text-zinc-700">
                <?= e($size) ?>
              </span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </article>
  </div>
</section>
