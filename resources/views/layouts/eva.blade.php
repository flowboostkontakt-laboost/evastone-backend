@php
    $loc = $locale ?? app()->getLocale();
    $sections = config('evastone.catalog_sections');
    $section = $sections[$loc] ?? 'schmuck';
    $others = array_values(array_diff(['de','en','pl'], [$loc]));
    $tr = [
        'de' => ['tagline'=>'Autorenschmuck in 925 Silber für unabhängige Boutiquen','skip'=>'Zum Inhalt springen','menu'=>'Menu','close'=>'Menü schließen','haendler'=>'B2B-Portal','nav'=>['Schmuck'=>'/de/schmuck/','Atelier'=>'/de/atelier/','Wissenswertes'=>'/de/wissenswertes/','Verkaufsstellen'=>'/de/haendlerkarte/','Messen'=>'/de/messe/','B2B'=>'/de/wholesale/','Kontakt'=>'/de/kontakt/'],'legalTitle'=>'Rechtliches','navTitle'=>'Navigation','nlTitle'=>'Newsletter','nlText'=>'Neue Modelle, Messetermine, Hinweise zur Pflege. Zwei Ausgaben im Monat.','nlBtn'=>'Abonnieren','nlMail'=>'E-Mail-Adresse','langNames'=>['de'=>'Deutsch','en'=>'English','pl'=>'Polski']],
        'en' => ['tagline'=>'Author jewellery in 925 silver for independent boutiques','skip'=>'Skip to content','menu'=>'Menu','close'=>'Close menu','haendler'=>'B2B portal','nav'=>['Jewellery'=>'/en/jewellery/','Atelier'=>'/en/atelier/','Knowledge'=>'/en/knowledge/','Stockists'=>'/en/stockists/','Trade fairs'=>'/en/trade-fairs/','B2B'=>'/en/wholesale/','Contact'=>'/en/contact/'],'legalTitle'=>'Legal','navTitle'=>'Navigation','nlTitle'=>'Newsletter','nlText'=>'New models, trade-fair dates, care tips. Twice a month.','nlBtn'=>'Subscribe','nlMail'=>'Email address','langNames'=>['de'=>'Deutsch','en'=>'English','pl'=>'Polski']],
        'pl' => ['tagline'=>'Autorska biżuteria ze srebra 925 dla niezależnych butików','skip'=>'Przejdź do treści','menu'=>'Menu','close'=>'Zamknij menu','haendler'=>'Portal B2B','nav'=>['Biżuteria'=>'/pl/bizuteria/','Atelier'=>'/pl/atelier/','Wiedza'=>'/pl/wiedza/','Punkty sprzedaży'=>'/pl/mapa-dystrybutorow/','Targi'=>'/pl/targi/','B2B'=>'/pl/wholesale/','Kontakt'=>'/pl/kontakt/'],'legalTitle'=>'Informacje prawne','navTitle'=>'Nawigacja','nlTitle'=>'Newsletter','nlText'=>'Nowe modele, targi, pielęgnacja. Dwa razy w miesiącu.','nlBtn'=>'Zapisz się','nlMail'=>'Adres e-mail','langNames'=>['de'=>'Deutsch','en'=>'English','pl'=>'Polski']],
    ][$loc];
    $langHrefs = ['de'=>"/de/",'en'=>"/en/",'pl'=>"/pl/"];
@endphp
<!doctype html>
<html lang="{{ $loc }}" dir="ltr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>document.documentElement.className += ' eva-js';</script>
<title>@yield('title', 'EvaStone')</title>
<meta name="description" content="@yield('meta', $tr['tagline'])">
<meta name="robots" content="noindex">
<link rel="preload" href="/fonts/noto-sans-400.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&display=swap">
<link rel="stylesheet" href="/assets/app.css">
<link rel="stylesheet" href="/assets/eva-extra.css">
<link rel="icon" href="/logo/evastone-sygnet-maska.png" type="image/png">
<meta name="theme-color" content="#efe7da">
@yield('head')
</head>

<body class="min-h-dvh flex flex-col bg-papier text-tusz">
<div class="eva-noise" aria-hidden="true"></div>
<a class="skip" href="#tresc">{{ $tr['skip'] }}</a>

<header x-data="nawigacja()" @keydown.escape.window="menu = false">
  <div class="sticky top-0 lg:static z-40 bg-papier/95 backdrop-blur-md lg:bg-papier lg:backdrop-blur-none border-b border-linia">
    <div class="siatka items-center py-3 lg:py-4">
      <a href="/{{ $loc }}/" class="col-span-7 md:col-span-5 flex items-center gap-3.5 no-underline text-tusz">
        <span class="sygnet w-9 h-9 shrink-0 lg:hidden" role="img" aria-label="Signet EvS"></span>
        <span class="min-w-0">
          <img src="/logo/evastone-wordmark.svg" alt="EvaStone" width="132" height="20" class="h-4 lg:h-[1.2rem] w-auto" fetchpriority="high">
          <span class="block text-[0.67rem] text-glos leading-tight mt-1 tracking-wide">{{ $tr['tagline'] }}</span>
        </span>
      </a>
      <div class="col-span-5 md:col-span-7 flex items-center justify-end gap-2.5 md:gap-5">
        <div class="eva-lang hidden md:flex items-center" role="group" aria-label="Language">
          <a aria-current="true">{{ $loc }}</a>
          @foreach ($others as $o)
            <a href="{{ $langHrefs[$o] }}" hreflang="{{ $o }}">{{ $o }}</a>
          @endforeach
        </div>
        <a href="/{{ $loc }}/wholesale/" class="eva-b2b hidden md:inline-flex">{{ $tr['haendler'] }}</a>
        <button type="button" class="lg:hidden inline-flex items-center gap-2.5 min-h-11 px-3 border border-tusz text-caption font-bold tracking-[0.14em] uppercase text-tusz bg-transparent cursor-pointer"
                @click="menu = !menu" :aria-expanded="menu.toString()" aria-controls="menu-glowne">
          <span x-text="menu ? '{{ $tr['close'] }}' : '{{ $tr['menu'] }}'">{{ $tr['menu'] }}</span>
          <span class="relative w-4 h-3 shrink-0" aria-hidden="true">
            <span class="absolute left-0 top-0 w-4 h-px bg-current transition-transform duration-300" :class="menu ? 'translate-y-[5.5px] rotate-45' : ''"></span>
            <span class="absolute left-0 top-[5.5px] w-4 h-px bg-current transition-opacity duration-200" :class="menu ? 'opacity-0' : ''"></span>
            <span class="absolute left-0 bottom-0 w-4 h-px bg-current transition-transform duration-300" :class="menu ? '-translate-y-[5.5px] -rotate-45' : ''"></span>
          </span>
        </button>
      </div>
    </div>
  </div>

  {{-- górne menu (desktop) — plan §3.1, złoty aktywny/hover --}}
  <nav class="hidden lg:block" aria-label="Hauptnavigation" style="border-bottom:1px solid var(--eva-linia)">
    <div class="siatka">
      <ul class="col-span-12 eva-nav" style="padding:.85rem 0">
        @foreach ($tr['nav'] as $label => $href)
          @php $hp = trim($href, '/'); $isActive = request()->is($hp) || request()->is($hp.'/*'); @endphp
          <li><a href="{{ $href }}" @if($isActive) aria-current="page" @endif>{{ $label }}</a></li>
        @endforeach
      </ul>
    </div>
  </nav>

  <div id="menu-glowne" x-show="menu" x-cloak style="display:none" class="fixed inset-0 z-50">
    <div class="absolute inset-0 bg-tusz/35" aria-hidden="true" @click="menu = false" x-transition.opacity.duration.250ms></div>
    <div class="absolute inset-y-0 left-0 w-full max-w-[30rem] bg-tusz text-papier overflow-y-auto ziarno ziarno-lekkie flex flex-col"
         x-transition:enter="transition-transform duration-300 ease-out" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition-transform duration-250 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
      <div class="flex items-center justify-between px-7 md:px-10 pt-6">
        <span class="sygnet w-9 h-9 text-papier" role="img" aria-label="Signet EvS"></span>
        <button type="button" class="p-2 bg-transparent border-0 cursor-pointer text-papier/80 hover:text-papier" @click="menu = false" aria-label="{{ $tr['close'] }}">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M5 5l14 14M19 5L5 19"/></svg>
        </button>
      </div>
      <nav class="px-7 md:px-10 py-8 flex-1" aria-label="Navigation">
        <ul class="list-none p-0 m-0">
          @foreach ($tr['nav'] as $label => $href)
            <li class="border-b border-papier/15">
              <a href="{{ $href }}" class="group flex items-center py-3.5 md:py-4 no-underline text-papier">
                <span class="font-display text-h1 leading-none group-hover:translate-x-2 transition-transform duration-300">{{ $label }}</span>
              </a>
            </li>
          @endforeach
        </ul>
      </nav>
      <div class="px-7 md:px-10 pb-8">
        <a href="mailto:office@evastone.eu" class="link-podkreslony text-papier/85 text-body inline-block">office@evastone.eu</a>
      </div>
    </div>
  </div>
</header>

<main id="tresc" class="flex-1">
  @yield('content')
</main>

<footer class="border-t border-tusz pt-14 pb-10 mt-auto" aria-label="Footer">
  <div class="siatka gap-y-12">
    <div class="col-span-12 md:col-span-4">
      <img src="/logo/evastone-wordmark.svg" alt="EvaStone" width="190" height="29" class="h-5 w-auto" loading="lazy">
      <p class="text-caption text-glos mt-3">{{ $tr['tagline'] }}</p>
      <address class="not-italic mt-5 text-body text-tusz leading-relaxed">
        UL. Potokowa 22A, 80-283 Gdańsk<br>
        <a href="tel:+48583480358" class="link-nav text-tusz">+48 58 348 03 58</a><br>
        <a href="mailto:office@evastone.eu" class="link-podkreslony text-tusz">office@evastone.eu</a>
      </address>
    </div>
    <nav class="col-span-12 sm:col-span-6 md:col-span-4 md:col-start-6" aria-label="{{ $tr['navTitle'] }}">
      <p class="etykieta punca text-glos">{{ $tr['navTitle'] }}</p>
      <ul class="mt-5 list-none p-0 m-0 columns-2 gap-x-8">
        @foreach ($tr['nav'] as $label => $href)
          <li class="py-1.5 break-inside-avoid"><a href="{{ $href }}" class="link-nav text-tusz text-body">{{ $label }}</a></li>
        @endforeach
      </ul>
    </nav>
    <div class="col-span-12 md:col-span-6 border-t border-linia pt-8">
      <h2 class="etykieta punca text-glos">{{ $tr['nlTitle'] }}</h2>
      <p class="text-caption text-glos mt-3 miara">{{ $tr['nlText'] }}</p>
      <form class="mt-5 flex flex-wrap items-end gap-3" method="post" action="/newsletter/subscribe">
        @csrf
        <input type="hidden" name="locale" value="{{ $loc }}">
        <div class="flex-1 min-w-[13rem] max-w-[22rem]">
          <label for="nl-mail" class="sr-only">{{ $tr['nlMail'] }}</label>
          <input id="nl-mail" name="email" type="email" required autocomplete="email" placeholder="{{ $tr['nlMail'] }}" class="pole">
        </div>
        <label class="sr-only"><input type="checkbox" name="consent" value="1" checked> ok</label>
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
        <button type="submit" class="btn btn-glowny">{{ $tr['nlBtn'] }}</button>
      </form>
      @if (session('newsletter_status'))<p class="text-caption text-tusz mt-2">{{ session('newsletter_status') }}</p>@endif
    </div>
    <div class="col-span-12 border-t border-linia pt-6 flex flex-wrap items-baseline justify-between gap-3">
      <p class="text-caption text-glos m-0">© {{ date('Y') }} EvaStone.</p>
      <p class="etykieta text-szept m-0">evastone.eu</p>
    </div>
  </div>
</footer>

<script type="module" src="/assets/app.js"></script>
<script src="/assets/eva-motion.js" defer></script>
</body>
</html>
