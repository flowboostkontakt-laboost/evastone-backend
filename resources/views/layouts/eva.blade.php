@php
    $loc = $locale ?? app()->getLocale();
    $sections = config('evastone.catalog_sections');
    $section = $sections[$loc] ?? 'schmuck';
    $nav = [
        'de' => ['tagline' => 'Autorenschmuck in 925 Silber für unabhängige Boutiquen', 'katalog' => 'Schmuck', 'haendler' => 'Für Händler: Konditionen', 'skip' => 'Zum Inhalt springen', 'menu' => 'Menu'],
        'en' => ['tagline' => 'Author jewellery in 925 silver for independent boutiques', 'katalog' => 'Jewellery', 'haendler' => 'For retailers: terms', 'skip' => 'Skip to content', 'menu' => 'Menu'],
        'pl' => ['tagline' => 'Autorska biżuteria ze srebra 925 dla niezależnych butików', 'katalog' => 'Biżuteria', 'haendler' => 'Dla butików: warunki', 'skip' => 'Przejdź do treści', 'menu' => 'Menu'],
    ][$loc] ?? [];
    $others = array_values(array_diff(['de','en','pl'], [$loc]));
@endphp
<!doctype html>
<html lang="{{ $loc }}" dir="ltr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'EvaStone — Katalog')</title>
<meta name="description" content="@yield('meta', 'Autorenschmuck in 925 Silber — erhältlich über unabhängige Boutiquen.')">
<meta name="robots" content="noindex">
<link rel="preload" href="/fonts/dm-serif-display-400.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/fonts/noto-sans-400.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/app.css">
<link rel="icon" href="/logo/evastone-sygnet-maska.png" type="image/png">
<meta name="theme-color" content="#efe7da">
@yield('head')
</head>

<body class="min-h-dvh flex flex-col bg-papier text-tusz">
<a class="skip" href="#tresc">{{ $nav['skip'] ?? 'Skip' }}</a>

<header x-data="nawigacja()" @keydown.escape.window="menu = false">
  <div class="sticky top-0 z-40 bg-papier/95 backdrop-blur-md border-b border-linia">
    <div class="siatka items-center py-3 lg:py-4">
      <a href="/{{ $loc }}/" class="col-span-7 md:col-span-5 flex items-center gap-3.5 no-underline text-tusz">
        <span class="sygnet w-9 h-9 shrink-0 lg:hidden" role="img" aria-label="Signet EvS"></span>
        <span class="min-w-0">
          <img src="/logo/evastone-wordmark.svg" alt="EvaStone" width="132" height="20" class="h-4 lg:h-[1.2rem] w-auto" fetchpriority="high">
          <span class="block text-[0.67rem] text-glos leading-tight mt-1 tracking-wide">{{ $nav['tagline'] ?? '' }}</span>
        </span>
      </a>
      <div class="col-span-5 md:col-span-7 flex items-center justify-end gap-2.5 md:gap-5">
        <div class="hidden md:flex items-center gap-1 text-caption" role="group" aria-label="Language">
          <span class="px-1.5 py-1 font-bold uppercase text-tusz" aria-current="true">{{ $loc }}</span>
          @foreach ($others as $o)
            <a href="/{{ $o }}/" hreflang="{{ $o }}" class="px-1.5 py-1 text-szept no-underline hover:text-tusz uppercase">{{ $o }}</a>
          @endforeach
        </div>
        <a href="/{{ $loc }}/" class="btn btn-glowny hidden md:inline-flex">{{ $nav['katalog'] ?? 'Katalog' }}</a>
      </div>
    </div>
  </div>
</header>

<main id="tresc" class="flex-1">
  @yield('content')
</main>

<footer class="border-t border-tusz pt-14 pb-10 mt-auto" aria-label="Footer">
  <div class="siatka gap-y-12">
    <div class="col-span-12 md:col-span-5">
      <img src="/logo/evastone-wordmark.svg" alt="EvaStone" width="190" height="29" class="h-5 w-auto" loading="lazy">
      <p class="text-caption text-glos mt-3">{{ $nav['tagline'] ?? '' }}</p>
      <a href="mailto:kontakt@evastone.eu" class="link-podkreslony text-tusz text-body mt-5 inline-block">kontakt@evastone.eu</a>
    </div>
    <div class="col-span-12 md:col-span-6 md:col-start-7 border-t border-linia md:border-t-0 pt-8 md:pt-0">
      <h2 class="etykieta punca text-glos">Newsletter</h2>
      <p class="text-caption text-glos mt-3 miara">{{ ['de'=>'Neue Modelle, Messetermine, Hinweise zur Pflege. Zwei Ausgaben im Monat.','en'=>'New models, trade-fair dates, care tips. Twice a month.','pl'=>'Nowe modele, targi, pielęgnacja. Dwa razy w miesiącu.'][$loc] ?? '' }}</p>
      <form class="mt-5 flex flex-wrap items-end gap-3" method="post" action="/newsletter/subscribe">
        @csrf
        <input type="hidden" name="locale" value="{{ $loc }}">
        <div class="flex-1 min-w-[13rem] max-w-[22rem]">
          <label for="nl-mail" class="sr-only">E-Mail</label>
          <input id="nl-mail" name="email" type="email" required autocomplete="email" placeholder="{{ ['de'=>'E-Mail-Adresse','en'=>'Email address','pl'=>'Adres e-mail'][$loc] ?? 'Email' }}" class="pole">
        </div>
        <label class="sr-only"><input type="checkbox" name="consent" value="1" checked> zgoda</label>
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
        <button type="submit" class="btn btn-glowny">{{ ['de'=>'Abonnieren','en'=>'Subscribe','pl'=>'Zapisz się'][$loc] ?? 'Subscribe' }}</button>
      </form>
      @if (session('newsletter_status'))
        <p class="text-caption text-tusz mt-2">{{ session('newsletter_status') }}</p>
      @endif
    </div>
    <div class="col-span-12 border-t border-linia pt-6 flex flex-wrap items-baseline justify-between gap-3">
      <p class="text-caption text-glos m-0">© {{ date('Y') }} EvaStone.</p>
      <p class="etykieta text-szept m-0">evastone.eu</p>
    </div>
  </div>
</footer>

<script type="module" src="/assets/app.js"></script>
</body>
</html>
