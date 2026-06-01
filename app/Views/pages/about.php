<section class="relative h-[24vh] min-h-[150px] overflow-hidden border-b border-emerald-950/10 bg-cover bg-center sm:h-[38vh] sm:min-h-[220px]" style="background-image:linear-gradient(rgba(2,38,1,.4), rgba(2,38,1,.4)), url('<?= e(asset('images/banners/inner-hero.png')) ?>');">
  <div class="site-shell flex h-full items-center">
    <h1 class="font-display text-4xl font-extrabold uppercase tracking-widest text-white sm:text-5xl">Sobre nosotros</h1>
  </div>
</section>

<section class="site-shell py-10">
  <div class="text-center">
    <h2 class="section-chip">Nuestra historia</h2>
  </div>

  <div class="mt-8 grid items-center gap-8 lg:grid-cols-2">
    <div class="space-y-4 text-sm leading-7 text-zinc-700 sm:text-base">
      <p>
        Mazal es una empresa familiar importadora de artículos de ferretería, ubicada en Buenos Aires y con clientes en todo el país. Su historia comienza en 1989, cuando Salvador Kurzrok y Silvia Ribke dan los primeros pasos en el rubro, iniciando la importación de cadena bolita desde Brasil.
      </p>
      <p>
        Con el paso de los años, la empresa fue creciendo y ampliando su catálogo, incorporando productos de jardinería y diversos artículos de ferretería fabricados en China y Taiwán.
      </p>
    </div>
    <img
      src="<?= e(asset('images/about-story-1.png')) ?>"
      alt="Company history"
      class="h-full w-full rounded-xl border border-emerald-950/10 object-cover shadow-sm">
  </div>

  <div class="mt-8 grid items-center gap-8 lg:grid-cols-2">
    <img
      src="<?= e(asset('images/about-story-2.jpeg')) ?>"
      alt="Warehouse and operations"
      class="order-2 h-full w-full rounded-xl border border-emerald-950/10 object-cover shadow-sm lg:order-1">
    <div class="order-1 space-y-4 text-sm leading-7 text-zinc-700 sm:text-base lg:order-2">
      <p>
        El legado familiar continúa con Liliana, la hija mayor, junto a su esposo Claudio Peker, quienes acompañaron el crecimiento y la consolidación del proyecto. En 2019, Liliana y Ariel fundan Mazal Importaciones SRL, dando origen al nombre de la empresa como un homenaje a sus raíces familiares.
      </p>
      <p>
        Hoy, con más de 30 años de experiencia, Mazal se destaca por ofrecer atención personalizada, entrega inmediata y productos de calidad, construyendo relaciones de confianza a largo plazo con sus clientes.
      </p>
    </div>
  </div>
</section>

<section class="site-shell pb-12">
  <div class="text-center">
    <h2 class="section-chip">Quienes somos</h2>
  </div>

  <div class="mt-7 grid gap-6 sm:grid-cols-2">
    <article class="catalog-card p-6 text-center">
      <img src="<?= e(asset('images/about-ariel.jpg')) ?>" alt="Ariel" class="mx-auto h-36 w-36 rounded-full object-cover">
      <h3 class="mt-4 text-2xl font-extrabold uppercase text-mazal-forest">Ariel</h3>
      <p class="text-sm font-semibold uppercase tracking-wider text-zinc-500">Managing Partner</p>
    </article>
    <article class="catalog-card p-6 text-center">
      <img src="<?= e(asset('images/about-liliana.jpg')) ?>" alt="Liliana" class="mx-auto h-36 w-36 rounded-full object-cover">
      <h3 class="mt-4 text-2xl font-extrabold uppercase text-mazal-forest">Liliana</h3>
      <p class="text-sm font-semibold uppercase tracking-wider text-zinc-500">Managing Partner</p>
    </article>
  </div>
</section>
