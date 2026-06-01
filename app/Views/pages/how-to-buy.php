<?php
$steps = [
  [
    'title' => '1. Buscá los productos',
    'description' => 'Navegá las categorias y conocé todos los productos del catálogo.',
    'image' => 'images/how-to-buy-step-1.png',
  ],
  [
    'title' => '2. Armá tu pedido',
    'description' => 'Elegí los productos y armá tu pedido aclarando medida, color y cantidad.',
    'image' => 'images/how-to-buy-step-2.png',
  ],
  [
    'title' => '3. Hacé tu pedido',
    'description' => 'Envianos los detalles de tu pedido por mail a pedidos@mazalimportaciones.com.ar.',
    'image' => 'images/how-to-buy-step-3.png',
  ],
  [
    'title' => '4. Recibí tu pedido',
    'description' => 'Nos contactamos con vos para coordinar la fecha de entrega.',
    'image' => 'images/how-to-buy-step-4.png',
  ],
];

$faqItems = [
  [
    'question' => '¿Cuál es el tiempo estimado de entrega?',
    'answer' => 'El tiempo de entrega es variable. Depende de la cantidad de productos solicitados, el stock disponible y la zona geográfica.',
  ],
  [
    'question' => '¿Cuáles son los métodos de pago aceptados?',
    'answer' => 'Aceptamos efectivo, transferencia bancaria y cheques a menos de 30 días. Consultanos también para trabajar con cuenta corriente.'
  ],
  [
    'question' => '¿Hay descuentos especiales por cantidad?',
    'answer' => 'Según el volumen del pedido, podemos armar un precio promocional. Contactanos y te preparamos una propuesta personalizada.',
  ],
];
?>

<section class="relative h-[24vh] min-h-[150px] overflow-hidden border-b border-emerald-950/10 bg-cover bg-center sm:h-[38vh] sm:min-h-[220px]" style="background-image:linear-gradient(rgba(2,38,1,.4), rgba(2,38,1,.4)), url('<?= e(asset('images/banners/inner-hero.png')) ?>');">
  <div class="site-shell flex h-full items-center">
    <h1 class="font-display text-4xl font-extrabold uppercase tracking-widest text-white sm:text-5xl">Como comprar</h1>
  </div>
</section>

<section class="site-shell py-10">
  <div class="text-center">
    <h2 class="section-chip">Cómo hacer tu pedido</h2>
  </div>

  <div class="mx-auto mt-8 flex flex-wrap justify-center gap-4 py-2">
    <?php foreach ($steps as $step): ?>
      <article class="w-full max-w-[260px] rounded-xl border border-zinc-300 bg-white px-6 py-6 text-center">
        <img
          src="<?= e(asset((string)$step['image'])) ?>"
          alt="<?= e((string)$step['title']) ?>"
          class="mx-auto h-12 w-auto">
        <h3 class="mt-4 text-base font-semibold text-zinc-900"><?= e((string)$step['title']) ?></h3>
        <p class="mt-1 text-sm leading-6 text-zinc-900"><?= e((string)$step['description']) ?></p>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="site-shell pb-12">
  <div class="mx-auto max-w-3xl">
    <div class="text-center">
      <h2 class="section-chip">Preguntas frecuentes</h2>
    </div>

    <div class="mt-7 space-y-3" data-faq-accordion>
      <?php foreach ($faqItems as $index => $faq): ?>
        <?php
        $buttonId = 'faq-button-' . $index;
        $panelId = 'faq-panel-' . $index;
        ?>
        <article class="overflow-hidden rounded-xl border border-emerald-950/10 bg-white shadow-sm" data-faq-item>
          <button
            id="<?= e($buttonId) ?>"
            type="button"
            class="flex w-full items-center justify-between gap-4 px-4 py-4 text-left sm:px-5"
            aria-expanded="false"
            aria-controls="<?= e($panelId) ?>"
            data-faq-trigger>
            <span class="text-sm font-extrabold uppercase tracking-wide text-mazal-forest sm:text-base"><?= e((string)$faq['question']) ?></span>
            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-emerald-950/20 bg-emerald-50/70">
              <svg
                class="h-4 w-4 text-mazal-forest transition-transform duration-300 ease-in-out"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
                data-faq-chevron>
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 011.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd"></path>
              </svg>
            </span>
          </button>
          <div
            id="<?= e($panelId) ?>"
            role="region"
            aria-labelledby="<?= e($buttonId) ?>"
            aria-hidden="true"
            class="overflow-hidden px-4 text-sm leading-6 text-zinc-700 opacity-0 transition-[max-height,opacity] duration-300 ease-in-out sm:px-5"
            style="max-height:0;"
            data-faq-panel>
            <div class="pb-4 sm:pb-5">
              <?= e((string)$faq['answer']) ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="relative overflow-hidden border-y border-emerald-950/10 bg-cover bg-center py-16" style="background-image:linear-gradient(rgba(2,38,1,.45), rgba(2,38,1,.45)), url('<?= e(asset('images/banners/cta-warehouse.png')) ?>');">
  <div class="site-shell text-center text-white">
    <h3 class="font-display text-3xl font-extrabold uppercase tracking-wider">Necesitas ayuda para comprar?</h3>
    <a href="<?= e(url('/contact')) ?>" class="pill-button mt-6">Contactanos</a>
  </div>
</section>

<script>
  (function() {
    var accordion = document.querySelector('[data-faq-accordion]');
    if (!accordion) return;

    var items = accordion.querySelectorAll('[data-faq-item]');

    function closeItem(item) {
      var trigger = item.querySelector('[data-faq-trigger]');
      var panel = item.querySelector('[data-faq-panel]');
      var chevron = item.querySelector('[data-faq-chevron]');
      if (!trigger || !panel || !chevron) return;

      trigger.setAttribute('aria-expanded', 'false');
      panel.setAttribute('aria-hidden', 'true');
      panel.style.maxHeight = '0px';
      panel.classList.remove('opacity-100');
      panel.classList.add('opacity-0');
      chevron.style.transform = 'rotate(0deg)';
    }

    function openItem(item) {
      var trigger = item.querySelector('[data-faq-trigger]');
      var panel = item.querySelector('[data-faq-panel]');
      var chevron = item.querySelector('[data-faq-chevron]');
      if (!trigger || !panel || !chevron) return;

      trigger.setAttribute('aria-expanded', 'true');
      panel.setAttribute('aria-hidden', 'false');
      panel.classList.remove('opacity-0');
      panel.classList.add('opacity-100');
      panel.style.maxHeight = panel.scrollHeight + 'px';
      chevron.style.transform = 'rotate(180deg)';
    }

    items.forEach(function(item) {
      closeItem(item);

      var trigger = item.querySelector('[data-faq-trigger]');
      if (!trigger) return;

      trigger.addEventListener('click', function() {
        var isOpen = trigger.getAttribute('aria-expanded') === 'true';

        items.forEach(function(otherItem) {
          if (otherItem !== item) {
            closeItem(otherItem);
          }
        });

        if (isOpen) {
          closeItem(item);
          return;
        }

        openItem(item);
      });
    });

    window.addEventListener('resize', function() {
      items.forEach(function(item) {
        var trigger = item.querySelector('[data-faq-trigger]');
        var panel = item.querySelector('[data-faq-panel]');
        if (!trigger || !panel) return;
        if (trigger.getAttribute('aria-expanded') === 'true') {
          panel.style.maxHeight = panel.scrollHeight + 'px';
        }
      });
    });
  })();
</script>