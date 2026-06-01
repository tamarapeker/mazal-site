<section class="relative h-[24vh] min-h-[150px] overflow-hidden border-b border-emerald-950/10 bg-cover bg-center sm:h-[38vh] sm:min-h-[220px]" style="background-image:linear-gradient(rgba(2,38,1,.4), rgba(2,38,1,.4)), url('<?= e(asset('images/banners/inner-hero.png')) ?>');">
  <div class="site-shell flex h-full items-center">
    <h1 class="font-display text-4xl font-extrabold uppercase tracking-widest text-white sm:text-5xl">Contacto</h1>
  </div>
</section>

<section class="site-shell py-10">
  <div class="text-center">
    <h2 class="section-chip">Ampliá tu catalogo con Mazal</h2>
    <p class="mt-3 text-sm font-semibold text-mazal-forest sm:text-base">
      Dejanos tus datos para que podamos contactarte
    </p>
  </div>

  <div class="mx-auto mt-7 max-w-xl rounded-xl border border-emerald-950/10 bg-white p-5 shadow-sm sm:p-6">
    <form action="#" method="post" class="grid gap-4">
      <div class="grid gap-1">
        <label class="text-xs font-bold uppercase tracking-wide text-zinc-600" for="full_name">Nombre y apellido</label>
        <input id="full_name" name="full_name" type="text" class="rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none transition focus:border-mazal-green focus:ring-4 focus:ring-mazal-lime/30">
      </div>

      <div class="grid gap-1">
        <label class="text-xs font-bold uppercase tracking-wide text-zinc-600" for="phone">Telefono</label>
        <input id="phone" name="phone" type="text" class="rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none transition focus:border-mazal-green focus:ring-4 focus:ring-mazal-lime/30">
      </div>

      <div class="grid gap-1">
        <label class="text-xs font-bold uppercase tracking-wide text-zinc-600" for="email">Email</label>
        <input id="email" name="email" type="email" class="rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none transition focus:border-mazal-green focus:ring-4 focus:ring-mazal-lime/30">
      </div>

      <div class="grid gap-1">
        <label class="text-xs font-bold uppercase tracking-wide text-zinc-600" for="province">Provincia</label>
        <input id="province" name="province" type="text" class="rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none transition focus:border-mazal-green focus:ring-4 focus:ring-mazal-lime/30">
      </div>

      <fieldset class="grid gap-2 rounded-md border border-zinc-200 p-3">
        <legend class="px-1 text-xs font-bold uppercase tracking-wide text-zinc-600">Tipo de punto de venta</legend>
        <label class="flex items-center gap-2 text-sm text-zinc-700">
          <input type="radio" name="store_type" class="h-4 w-4 border-zinc-300 text-mazal-green focus:ring-mazal-lime" checked>
          Ferreteria al publico
        </label>
        <label class="flex items-center gap-2 text-sm text-zinc-700">
          <input type="radio" name="store_type" class="h-4 w-4 border-zinc-300 text-mazal-green focus:ring-mazal-lime">
          Distribuidor
        </label>
        <label class="flex items-center gap-2 text-sm text-zinc-700">
          <input type="radio" name="store_type" class="h-4 w-4 border-zinc-300 text-mazal-green focus:ring-mazal-lime">
          Mayorista
        </label>
      </fieldset>

      <button type="submit" class="mt-2 rounded-md bg-mazal-green px-4 py-3 text-sm font-extrabold uppercase tracking-wide text-white transition hover:bg-mazal-forest">
        Enviar
      </button>
    </form>
  </div>
</section>

<section class="site-shell pb-12">
  <div class="mx-auto max-w-xl rounded-xl border-l-4 border-mazal-lime bg-white p-5 shadow-sm">
    <h3 class="text-xl font-extrabold uppercase text-mazal-forest">Canales directos</h3>
    <p class="mt-2 text-sm text-zinc-600">Tambien podes escribirnos por cualquiera de estos medios:</p>

    <div class="mt-4 grid gap-3">
      <a href="https://wa.link/fex4d4" target="_blank" rel="noreferrer" class="inline-flex items-center gap-3 rounded-lg border border-emerald-950/10 px-3 py-2 text-sm font-semibold text-zinc-700 transition hover:border-mazal-green hover:bg-emerald-50">
        <img src="<?= e(asset('icons/social/whatsapp.svg')) ?>" alt="WhatsApp" class="h-5 w-5">
        <span>WhatsApp: +54 9 11 4537 6452</span>
      </a>

      <a href="mailto:info@mazalimportaciones.com" class="inline-flex items-center gap-3 rounded-lg border border-emerald-950/10 px-3 py-2 text-sm font-semibold text-zinc-700 transition hover:border-mazal-green hover:bg-emerald-50">
        <img src="<?= e(asset('icons/social/mail.svg')) ?>" alt="Email" class="h-5 w-5">
        <span>Email: info@mazalimportaciones.com</span>
      </a>

      <a href="https://instagram.com/mazal_importaciones_srl" target="_blank" rel="noreferrer" class="inline-flex items-center gap-3 rounded-lg border border-emerald-950/10 px-3 py-2 text-sm font-semibold text-zinc-700 transition hover:border-mazal-green hover:bg-emerald-50">
        <img src="<?= e(asset('icons/social/instagram.svg')) ?>" alt="Instagram" class="h-5 w-5">
        <span>Instagram: @mazal_importaciones_srl</span>
      </a>
    </div>
  </div>
</section>
