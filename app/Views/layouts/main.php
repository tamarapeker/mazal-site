<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(($title ?? '') . ' | ' . config('app.name', 'Mazal Catalog')) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;700;800&family=Nunito+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>

<body class="min-h-screen">
  <header class="sticky top-0 z-40 border-b border-emerald-950/10 bg-mazal-gray/95 backdrop-blur">
    <div class="relative">
      <a href="<?= e(url('/')) ?>" class="absolute left-4 top-1/2 z-10 hidden -translate-y-1/2 items-center gap-3 sm:left-6 lg:left-8 md:flex">
        <img src="<?= e(asset('images/brand/logo.png')) ?>" alt="Mazal Importaciones" class="h-10 w-auto">
      </a>

      <div class="site-shell">
        <div class="flex items-center justify-between py-3 md:justify-end">
          <a href="<?= e(url('/')) ?>" class="flex items-center gap-3 md:hidden">
            <img src="<?= e(asset('images/brand/logo.png')) ?>" alt="Mazal Importaciones" class="h-10 w-auto">
          </a>
          <button
            id="mobile-menu-toggle"
            type="button"
            class="inline-flex items-center rounded-md border border-mazal-forest/20 px-3 py-2 text-xs font-extrabold uppercase tracking-wide text-mazal-forest md:hidden"
            aria-expanded="false"
            aria-controls="mobile-navigation"
            aria-label="Open navigation menu">
            <span class="sr-only">Menu</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
          </button>

          <nav id="main-navigation" class="hidden items-center gap-1 md:flex">
            <a href="<?= e(url('/')) ?>" class="nav-link <?= is_current_path('/') ? 'bg-mazal-lime/30' : '' ?>">Home</a>
            <a href="<?= e(url('/categories')) ?>" class="nav-link <?= is_current_path('/categories') ? 'bg-mazal-lime/30' : '' ?>">Catalogo</a>
            <a href="<?= e(url('/contact')) ?>" class="nav-link <?= is_current_path('/contact') ? 'bg-mazal-lime/30' : '' ?>">Contacto</a>
            <a href="<?= e(url('/how-to-buy')) ?>" class="nav-link <?= in_array(request_path(), ['/how-to-buy', '/como-comprar'], true) ? 'bg-mazal-lime/30' : '' ?>">Cómo comprar</a>
            <a href="<?= e(url('/about')) ?>" class="nav-link <?= is_current_path('/about') ? 'bg-mazal-lime/30' : '' ?>">Sobre nosotros</a>
          </nav>
        </div>

        <nav id="mobile-navigation" class="mobile-menu-panel absolute right-4 top-full z-50 mt-2 w-[65vw] max-w-xs rounded-xl border border-emerald-950/10 bg-mazal-gray/95 py-2 shadow-lg backdrop-blur sm:right-6 md:hidden" aria-hidden="true">
          <div class="flex flex-col gap-1 px-2">
            <a href="<?= e(url('/')) ?>" class="nav-link <?= is_current_path('/') ? 'bg-mazal-lime/30' : '' ?>">Home</a>
            <a href="<?= e(url('/categories')) ?>" class="nav-link <?= is_current_path('/categories') ? 'bg-mazal-lime/30' : '' ?>">Catalogo</a>
            <a href="<?= e(url('/contact')) ?>" class="nav-link <?= is_current_path('/contact') ? 'bg-mazal-lime/30' : '' ?>">Contacto</a>
            <a href="<?= e(url('/how-to-buy')) ?>" class="nav-link <?= in_array(request_path(), ['/how-to-buy', '/como-comprar'], true) ? 'bg-mazal-lime/30' : '' ?>">Cómo comprar</a>
            <a href="<?= e(url('/about')) ?>" class="nav-link <?= is_current_path('/about') ? 'bg-mazal-lime/30' : '' ?>">Sobre nosotros</a>
          </div>
        </nav>
      </div>
    </div>
  </header>

  <main class="pb-12">
    <?= $content ?>
  </main>

  <footer class="border-t border-emerald-950/10 bg-mazal-gray py-10">
    <div class="site-shell grid gap-8 md:grid-cols-[2fr_1fr]">
      <div>
        <h4 class="mb-3 font-display text-base font-extrabold uppercase tracking-wider text-mazal-forest">Navegacion</h4>
        <div class="grid gap-2 text-sm font-semibold text-zinc-800">
          <a href="<?= e(url('/categories')) ?>" class="hover:text-mazal-green hover:underline">Catalogo</a>
          <a href="<?= e(url('/contact')) ?>" class="hover:text-mazal-green hover:underline">Contacto</a>
          <a href="<?= e(url('/how-to-buy')) ?>" class="hover:text-mazal-green hover:underline">Cómo comprar</a>
          <a href="<?= e(url('/about')) ?>" class="hover:text-mazal-green hover:underline">Sobre nosotros</a>
        </div>
      </div>

      <div>
        <h4 class="mb-3 font-display text-base font-extrabold uppercase tracking-wider text-mazal-forest">Seguinos en redes</h4>
        <div class="flex flex-wrap items-center gap-3">
          <a class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-zinc-400 bg-white text-zinc-700 transition hover:border-mazal-green hover:bg-emerald-50" href="https://instagram.com/mazal_importaciones_srl" target="_blank" rel="noreferrer">
            <img src="<?= e(asset('icons/social/instagram.svg')) ?>" alt="Instagram" class="h-4 w-4">
          </a>
          <a class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-zinc-400 bg-white text-zinc-700 transition hover:border-mazal-green hover:bg-emerald-50" href="https://www.youtube.com/@ArielPeker" target="_blank" rel="noreferrer">
            <img src="<?= e(asset('icons/social/youtube.svg')) ?>" alt="YouTube" class="h-4 w-4">
          </a>
          <a class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-zinc-400 bg-white text-zinc-700 transition hover:border-mazal-green hover:bg-emerald-50" href="https://wa.link/fex4d4" target="_blank" rel="noreferrer">
            <img src="<?= e(asset('icons/social/whatsapp.svg')) ?>" alt="WhatsApp" class="h-4 w-4">
          </a>
        </div>
      </div>
    </div>
    <div class="site-shell mt-8 border-t border-emerald-950/10 pt-4 text-xs font-bold uppercase tracking-wider text-zinc-500">
      <?= e(config('app.name', 'Mazal Catalog')) ?> (c) <?= date('Y') ?>
    </div>
  </footer>

  <script>
    (function() {
      var button = document.getElementById('mobile-menu-toggle');
      var menu = document.getElementById('mobile-navigation');
      if (!button || !menu) return;
      var closeTimer = null;
      var closeDurationMs = 360;

      function openMenu() {
        if (closeTimer !== null) {
          window.clearTimeout(closeTimer);
          closeTimer = null;
        }

        menu.classList.remove('is-closing');
        menu.classList.remove('invisible');
        menu.classList.add('is-open');

        button.setAttribute('aria-expanded', 'true');
        menu.setAttribute('aria-hidden', 'false');
      }

      function closeMenu() {
        if (!menu.classList.contains('is-open')) return;

        if (closeTimer !== null) {
          window.clearTimeout(closeTimer);
        }

        menu.classList.remove('is-open');
        menu.classList.add('is-closing');
        button.setAttribute('aria-expanded', 'false');
        menu.setAttribute('aria-hidden', 'true');

        closeTimer = window.setTimeout(function() {
          menu.classList.remove('is-closing');
          menu.classList.add('invisible');
          closeTimer = null;
        }, closeDurationMs);
      }

      button.addEventListener('click', function() {
        var isOpen = menu.classList.contains('is-open');
        if (isOpen) {
          closeMenu();
          return;
        }
        if (menu.classList.contains('is-closing')) {
          if (closeTimer !== null) {
            window.clearTimeout(closeTimer);
            closeTimer = null;
          }
          openMenu();
          return;
        }
        openMenu();
      });

      document.addEventListener('click', function(event) {
        if (menu.classList.contains('invisible')) return;
        if (menu.contains(event.target) || button.contains(event.target)) return;
        closeMenu();
      });

      window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
          closeMenu();
        }
      });
    })();
  </script>
</body>

</html>