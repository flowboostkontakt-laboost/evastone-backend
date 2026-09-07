@php
    $loc = $locale;
    $sec = ['de' => 'schmuck', 'en' => 'jewellery', 'pl' => 'bizuteria'][$loc] ?? 'schmuck';
    $t = [
        'de' => ['claim' => 'Schönheit ist die einzige Form der Perfektion, die uns gegeben ist.', 'kicker' => '925 Silber · Handarbeit seit 1995', 'stores' => 'Verkaufsstellen', 'b2b' => 'B2B-Portal', 'schmuck' => 'Schmuck', 'schmuckLead' => 'Sechs Kategorien, gefertigt in der eigenen Werkstatt.', 'more' => 'Mehr', 'atK' => 'Atelier', 'atH' => 'Kunst entsteht aus Liebe zur Schönheit.', 'atBody' => 'Die Werkstatt von Ewa und Rafał Szyszko in Gdańsk verbindet alte Goldschmiedetechnik mit eigenem Entwurf. Jedes Stück entsteht von Hand.', 'atBtn' => 'Das Atelier', 'mapK' => 'Verkaufsstellen', 'mapH' => 'Finden Sie ein Geschäft in Ihrer Nähe.', 'mapBtn' => 'Alle Verkaufsstellen', 'messenK' => 'Messen', 'messenH' => 'Wo Sie uns treffen.', 'messenBtn' => 'Termine ansehen'],
        'en' => ['claim' => 'Beauty is the only form of perfection given to us.', 'kicker' => '925 silver · handcrafted since 1995', 'stores' => 'Stockists', 'b2b' => 'B2B portal', 'schmuck' => 'Jewellery', 'schmuckLead' => 'Six categories, made in our own workshop.', 'more' => 'More', 'atK' => 'Atelier', 'atH' => 'Art is born of a love of beauty.', 'atBody' => 'The workshop of Ewa and Rafał Szyszko in Gdańsk joins old goldsmith craft with its own design. Every piece is made by hand.', 'atBtn' => 'The atelier', 'mapK' => 'Stockists', 'mapH' => 'Find a store near you.', 'mapBtn' => 'All stockists', 'messenK' => 'Trade fairs', 'messenH' => 'Where to meet us.', 'messenBtn' => 'See dates'],
        'pl' => ['claim' => 'Piękno jest jedyną formą doskonałości, jaka jest nam dana.', 'kicker' => 'srebro 925 · ręcznie od 1995', 'stores' => 'Punkty sprzedaży', 'b2b' => 'Portal B2B', 'schmuck' => 'Biżuteria', 'schmuckLead' => 'Sześć kategorii, wykonywane w naszej pracowni.', 'more' => 'Więcej', 'atK' => 'Pracownia', 'atH' => 'Sztuka powstaje z miłości do piękna.', 'atBody' => 'Pracownia Ewy i Rafała Szyszko w Gdańsku łączy dawne techniki złotnicze z własnym projektem. Każdy egzemplarz powstaje ręcznie.', 'atBtn' => 'Poznaj pracownię', 'mapK' => 'Punkty sprzedaży', 'mapH' => 'Znajdź sklep w pobliżu.', 'mapBtn' => 'Wszystkie punkty', 'messenK' => 'Targi', 'messenH' => 'Gdzie nas spotkasz.', 'messenBtn' => 'Zobacz terminy'],
    ][$loc];
    $stockist = ['de' => '/de/haendlerkarte/', 'en' => '/en/stockists/', 'pl' => '/pl/mapa-dystrybutorow/'][$loc];
    $messen = ['de' => '/de/messe/', 'en' => '/en/trade-fairs/', 'pl' => '/pl/targi/'][$loc];
@endphp

@extends('layouts.eva')

@section('title', 'EvaStone — '.$t['schmuck'])

@section('content')
{{-- EKRAN 1 — hero --}}
<section class="eva-hero">
  <img class="eva-hero__img" src="/img/photo/hero-partner-ring-am-fels-hoch-1280.webp" alt="EvaStone" fetchpriority="high">
  <div class="eva-hero__scrim" aria-hidden="true"></div>
  <div class="siatka eva-hero__in">
    <div class="col-span-12">
      <p class="eva-hero__kick">{{ $t['kicker'] }}</p>
      <p class="eva-hero__claim">{{ $t['claim'] }}</p>
      <div class="eva-hero__cta">
        <a href="{{ $stockist }}" class="btn btn-kontra">{{ $t['stores'] }}</a>
        <a href="/{{ $loc }}/wholesale/" class="btn btn-kontra-drugi">{{ $t['b2b'] }}</a>
      </div>
    </div>
  </div>
</section>

{{-- EKRAN 2 — kategorie na białym polu (produkty pływają) --}}
<section class="eva-sheet">
  <div class="siatka">
    <div class="col-span-12 eva-sheet-in">
      <header style="margin-bottom:2.5rem">
        <p class="eva-kick">{{ $t['schmuck'] }}</p>
        <p class="text-body text-glos" style="margin-top:.75rem">{{ $t['schmuckLead'] }}</p>
      </header>
      <ul class="eva-grid">
        @foreach ($tiles as $tile)
          <li>
            <a href="/{{ $loc }}/{{ $sec }}/{{ $tile['slug'] }}/" class="eva-tile">
              @if ($tile['image'])
                <img class="eva-tile__img" src="{{ $tile['image'] }}" alt="{{ $tile['name'] }}" loading="lazy" decoding="async">
              @else
                <span class="eva-tile__img" style="display:block;background:var(--eva-papier)"></span>
              @endif
              <span class="eva-tile__cap">
                <span class="eva-tile__name">{{ $tile['name'] }}</span>
                <span class="eva-tile__more">{{ $t['more'] }} →</span>
              </span>
            </a>
          </li>
        @endforeach
      </ul>
    </div>
  </div>
</section>

{{-- Pracownia / Atelier --}}
<section style="padding-block:4rem">
  <div class="siatka" style="align-items:center;row-gap:2rem">
    <div class="col-span-12 md:col-span-6">
      <img src="/img/photo/werkstatt-raum-1440.webp" alt="{{ $t['atK'] }}" style="width:100%;aspect-ratio:3/2;object-fit:cover;display:block" loading="lazy" decoding="async">
    </div>
    <div class="col-span-12 md:col-span-5 md:col-start-8">
      <p class="eva-kick">{{ $t['atK'] }}</p>
      <h2 class="text-display font-display" style="margin-top:1rem;text-wrap:balance">{{ $t['atH'] }}</h2>
      <p class="text-body text-glos" style="margin-top:1.25rem">{{ $t['atBody'] }}</p>
      <div style="margin-top:2rem"><a href="/{{ $loc }}/atelier/" class="btn btn-drugi">{{ $t['atBtn'] }}</a></div>
    </div>
  </div>
</section>

{{-- EKRAN 3 — mapa sklepów --}}
<section class="ziarno" style="background:var(--eva-tusz);color:var(--eva-papier);padding-block:4.5rem">
  <div class="siatka" style="align-items:center;row-gap:2rem">
    <div class="col-span-12 md:col-span-5">
      <p class="eva-kick" style="color:rgba(239,231,218,.7)">{{ $t['mapK'] }}</p>
      <h2 class="text-display font-display" style="margin-top:1rem;text-wrap:balance">{{ $t['mapH'] }}</h2>
      <div style="margin-top:2rem"><a href="{{ $stockist }}" class="btn btn-kontra">{{ $t['mapBtn'] }}</a></div>
    </div>
    <div class="col-span-12 md:col-span-6 md:col-start-7">
      <a href="{{ $stockist }}" aria-label="{{ $t['mapBtn'] }}" style="display:block;aspect-ratio:16/10;background:var(--eva-papier);position:relative">
        <span style="position:absolute;inset:0;display:grid;place-items:center;color:var(--eva-glos)">
          <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"><path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
        </span>
      </a>
    </div>
  </div>
</section>

{{-- EKRAN 4 — Messen --}}
<section style="padding-block:3.5rem">
  <div class="siatka" style="align-items:baseline;row-gap:1rem">
    <div class="col-span-12 md:col-span-8">
      <p class="eva-kick">{{ $t['messenK'] }}</p>
      <h2 class="text-display font-display" style="margin-top:.75rem;text-wrap:balance">{{ $t['messenH'] }}</h2>
    </div>
    <div class="col-span-12 md:col-span-4" style="text-align:left">
      <a href="{{ $messen }}" class="btn btn-drugi">{{ $t['messenBtn'] }}</a>
    </div>
  </div>
</section>
@endsection
