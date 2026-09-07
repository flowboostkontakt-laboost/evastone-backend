@php
    $loc = $locale;
    $section = config("evastone.catalog_sections.{$loc}");
    $t = [
        'de' => [
            'claim' => 'Schönheit ist die einzige Form der Perfektion, die uns gegeben ist.',
            'kicker' => '925 Silber · Handarbeit seit 1995',
            'stores' => 'Verkaufsstellen', 'b2b' => 'B2B-Portal',
            'schmuck' => 'Schmuck', 'schmuckLead' => 'Sechs Kategorien, gefertigt in der eigenen Werkstatt.',
            'atK' => 'Atelier', 'atH' => 'Kunst entsteht aus Liebe zur Schönheit.', 'atBody' => 'Die Autorenschmuck-Werkstatt von Ewa und Rafał Szyszko in Gdańsk verbindet alte Goldschmiedetechnik mit eigenem Entwurf. Jedes Stück entsteht von Hand.', 'atBtn' => 'Das Atelier', 'more' => 'Mehr',
            'mapK' => 'Verkaufsstellen', 'mapH' => 'Finden Sie ein Geschäft in Ihrer Nähe.', 'mapBtn' => 'Alle Verkaufsstellen',
            'messenK' => 'Messen', 'messenH' => 'Wo Sie uns treffen.', 'messenBtn' => 'Termine ansehen',
        ],
        'en' => [
            'claim' => 'Beauty is the only form of perfection given to us.',
            'kicker' => '925 silver · handcrafted since 1995',
            'stores' => 'Stockists', 'b2b' => 'B2B portal',
            'schmuck' => 'Jewellery', 'schmuckLead' => 'Six categories, made in our own workshop.',
            'atK' => 'Atelier', 'atH' => 'Art is born of a love of beauty.', 'atBody' => 'The author-jewellery workshop of Ewa and Rafał Szyszko in Gdańsk joins old goldsmith craft with its own design. Every piece is made by hand.', 'atBtn' => 'The atelier', 'more' => 'More',
            'mapK' => 'Stockists', 'mapH' => 'Find a store near you.', 'mapBtn' => 'All stockists',
            'messenK' => 'Trade fairs', 'messenH' => 'Where to meet us.', 'messenBtn' => 'See dates',
        ],
        'pl' => [
            'claim' => 'Piękno jest jedyną formą doskonałości, jaka jest nam dana.',
            'kicker' => 'srebro 925 · ręcznie od 1995',
            'stores' => 'Punkty sprzedaży', 'b2b' => 'Portal B2B',
            'schmuck' => 'Biżuteria', 'schmuckLead' => 'Sześć kategorii, wykonywane w naszej pracowni.',
            'atK' => 'Pracownia', 'atH' => 'Sztuka powstaje z miłości do piękna.', 'atBody' => 'Autorska pracownia Ewy i Rafała Szyszko w Gdańsku łączy dawne techniki złotnicze z własnym projektem. Każdy egzemplarz powstaje ręcznie.', 'atBtn' => 'Poznaj pracownię', 'more' => 'Więcej',
            'mapK' => 'Punkty sprzedaży', 'mapH' => 'Znajdź sklep w pobliżu.', 'mapBtn' => 'Wszystkie punkty',
            'messenK' => 'Targi', 'messenH' => 'Gdzie nas spotkasz.', 'messenBtn' => 'Zobacz terminy',
        ],
    ][$loc];
    $stockist = ['de' => '/de/haendlerkarte/', 'en' => '/en/stockists/', 'pl' => '/pl/mapa-dystrybutorow/'][$loc];
    $messen = ['de' => '/de/messe/', 'en' => '/en/trade-fairs/', 'pl' => '/pl/messe/'][$loc] ?? "/{$loc}/messe/";
@endphp

@extends('layouts.eva')

@section('title', 'EvaStone — '.$t['schmuck'])

@section('content')
{{-- EKRAN 1 — hero: jedno zdjęcie klienta, jedno zdanie, dwa wyjścia (VCA: biel/cisza) --}}
<section class="relative h-[86vh] min-h-[520px] flex items-end overflow-hidden">
  <img src="/img/photo/hero-partner-ring-am-fels-hoch-1280.webp" alt="EvaStone"
       class="absolute inset-0 w-full h-full object-cover object-center" fetchpriority="high">
  {{-- równy scrim + mocniejszy gradient u dołu = tekst zawsze czytelny --}}
  <div class="absolute inset-0 bg-tusz/25" aria-hidden="true"></div>
  <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-tusz/90 via-tusz/50 to-transparent" aria-hidden="true"></div>
  <div class="siatka relative w-full pb-16 md:pb-24">
    <div class="col-span-12 lg:col-span-9">
      <p class="etykieta punca text-papier/75 mb-5">925 Silber · Handarbeit seit 1995</p>
      <p class="font-display text-papier text-display leading-[1.08] text-balance max-w-[22ch]">{{ $t['claim'] }}</p>
      <div class="mt-9 flex flex-wrap gap-3">
        <a href="{{ $stockist }}" class="btn btn-kontra">{{ $t['stores'] }}</a>
        <a href="/{{ $loc }}/wholesale/" class="btn btn-kontra-drugi">{{ $t['b2b'] }}</a>
      </div>
    </div>
  </div>
</section>

{{-- EKRAN 2 — sześć kafli kategorii --}}
<section class="py-16 md:py-24">
  <div class="siatka">
    <header class="col-span-12 mb-8 md:mb-12">
      <p class="etykieta punca text-glos">{{ $t['schmuck'] }}</p>
      <p class="text-body text-glos mt-3 miara">{{ $t['schmuckLead'] }}</p>
    </header>
    <ul class="col-span-12 bg-white border-t border-l border-linia grid grid-cols-2 md:grid-cols-3 list-none p-0 m-0">
      @foreach ($tiles as $tile)
        <li class="border-r border-b border-linia">
          <a href="/{{ $loc }}/{{ $section }}/{{ $tile['slug'] }}/" class="group flex flex-col h-full no-underline text-tusz">
            <div class="overflow-hidden">
              @if ($tile['image'])
                <img src="{{ $tile['image'] }}" alt="{{ $tile['name'] }}"
                     class="w-full aspect-square object-contain p-6 md:p-8 transition-transform duration-700 ease-out group-hover:scale-[1.06]"
                     loading="lazy" decoding="async">
              @else
                <div class="w-full aspect-square bg-plotno"></div>
              @endif
            </div>
            <div class="mt-auto flex items-baseline justify-between gap-3 border-t border-linia px-5 py-4">
              <span class="font-display text-h2 leading-none">{{ $tile['name'] }}</span>
              <span class="etykieta text-szept group-hover:text-tusz transition-colors">{{ $t['more'] }} →</span>
            </div>
          </a>
        </li>
      @endforeach
    </ul>
  </div>
</section>

{{-- Pracownia / Atelier — jak na obecnej stronie --}}
<section class="pb-4 md:pb-8">
  <div class="siatka items-center gap-y-8">
    <div class="col-span-12 md:col-span-6">
      <img src="/img/photo/werkstatt-raum-1440.webp" alt="{{ $t['atK'] }}" class="w-full aspect-[3/2] object-cover" loading="lazy" decoding="async">
    </div>
    <div class="col-span-12 md:col-span-5 md:col-start-8">
      <p class="etykieta punca text-glos">{{ $t['atK'] }}</p>
      <h2 class="text-display mt-4 text-balance">{{ $t['atH'] }}</h2>
      <p class="text-body text-glos mt-5 miara">{{ $t['atBody'] }}</p>
      <div class="mt-8"><a href="/{{ $loc }}/atelier/" class="btn btn-drugi">{{ $t['atBtn'] }}</a></div>
    </div>
  </div>
</section>

{{-- EKRAN 3 — podgląd mapy sklepów --}}
<section class="py-16 md:py-24 bg-tusz text-papier ziarno">
  <div class="siatka items-center gap-y-8">
    <div class="col-span-12 md:col-span-5">
      <p class="etykieta punca text-papier/70">{{ $t['mapK'] }}</p>
      <h2 class="text-display mt-4 text-balance">{{ $t['mapH'] }}</h2>
      <div class="mt-8"><a href="{{ $stockist }}" class="btn btn-kontra">{{ $t['mapBtn'] }}</a></div>
    </div>
    <div class="col-span-12 md:col-span-6 md:col-start-7">
      <a href="{{ $stockist }}" class="block aspect-[16/10] bg-plotno relative overflow-hidden" aria-label="{{ $t['mapBtn'] }}">
        <span class="absolute inset-0 grid place-items-center text-glos">
          <svg class="w-12 h-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"><path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
        </span>
      </a>
    </div>
  </div>
</section>

{{-- EKRAN 4 — Messen (teaser); newsletter jest w stopce --}}
<section class="py-16 md:py-20">
  <div class="siatka items-baseline gap-y-4">
    <div class="col-span-12 md:col-span-8">
      <p class="etykieta punca text-glos">{{ $t['messenK'] }}</p>
      <h2 class="text-display mt-3 text-balance">{{ $t['messenH'] }}</h2>
    </div>
    <div class="col-span-12 md:col-span-4 md:text-right">
      <a href="{{ $messen }}" class="btn btn-drugi">{{ $t['messenBtn'] }}</a>
    </div>
  </div>
</section>
@endsection
