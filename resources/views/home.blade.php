@php
    $loc = $locale;
    $sec = ['de' => 'schmuck', 'en' => 'jewellery', 'pl' => 'bizuteria'][$loc] ?? 'schmuck';
    $t = [
        'de' => ['claim' => 'Schönheit ist die einzige Form der Perfektion, die uns gegeben ist.', 'kicker' => '925 Silber · Handarbeit seit 1995', 'stores' => 'Verkaufsstellen', 'b2b' => 'B2B-Portal', 'scroll' => 'Entdecken', 'schmuck' => 'Schmuck', 'schmuckLead' => 'Sechs Kategorien, gefertigt in der eigenen Werkstatt.', 'more' => 'Mehr', 'atK' => 'Atelier', 'atH' => 'Kunst entsteht aus Liebe zur Schönheit.', 'atBody' => 'Die Werkstatt von Ewa und Rafał Szyszko in Gdańsk verbindet alte Goldschmiedetechnik mit eigenem Entwurf. Jedes Stück entsteht von Hand.', 'atBtn' => 'Das Atelier', 'mapK' => 'Verkaufsstellen', 'mapH' => 'Finden Sie ein Geschäft in Ihrer Nähe.', 'mapBody' => 'Erhältlich über unabhängige Boutiquen in DE, AT, UK und CH. Wir verkaufen nicht direkt — Ihre Boutique tut es.', 'mapBtn' => 'Alle Verkaufsstellen', 'messenK' => 'Messen', 'messenH' => 'Wo Sie uns treffen.', 'messenBtn' => 'Termine ansehen'],
        'en' => ['claim' => 'Beauty is the only form of perfection given to us.', 'kicker' => '925 silver · handcrafted since 1995', 'stores' => 'Stockists', 'b2b' => 'B2B portal', 'scroll' => 'Discover', 'schmuck' => 'Jewellery', 'schmuckLead' => 'Six categories, made in our own workshop.', 'more' => 'More', 'atK' => 'Atelier', 'atH' => 'Art is born of a love of beauty.', 'atBody' => 'The workshop of Ewa and Rafał Szyszko in Gdańsk joins old goldsmith craft with its own design. Every piece is made by hand.', 'atBtn' => 'The atelier', 'mapK' => 'Stockists', 'mapH' => 'Find a store near you.', 'mapBody' => 'Available through independent boutiques in DE, AT, UK and CH. We do not sell directly — your boutique does.', 'mapBtn' => 'All stockists', 'messenK' => 'Trade fairs', 'messenH' => 'Where to meet us.', 'messenBtn' => 'See dates'],
        'pl' => ['claim' => 'Piękno jest jedyną formą doskonałości, jaka jest nam dana.', 'kicker' => 'srebro 925 · ręcznie od 1995', 'stores' => 'Punkty sprzedaży', 'b2b' => 'Portal B2B', 'scroll' => 'Odkryj', 'schmuck' => 'Biżuteria', 'schmuckLead' => 'Sześć kategorii, wykonywane w naszej pracowni.', 'more' => 'Więcej', 'atK' => 'Pracownia', 'atH' => 'Sztuka powstaje z miłości do piękna.', 'atBody' => 'Pracownia Ewy i Rafała Szyszko w Gdańsku łączy dawne techniki złotnicze z własnym projektem. Każdy egzemplarz powstaje ręcznie.', 'atBtn' => 'Poznaj pracownię', 'mapK' => 'Punkty sprzedaży', 'mapH' => 'Znajdź sklep w pobliżu.', 'mapBody' => 'Dostępne w niezależnych butikach w DE, AT, UK i CH. Nie sprzedajemy bezpośrednio — robi to Twój butik.', 'mapBtn' => 'Wszystkie punkty', 'messenK' => 'Targi', 'messenH' => 'Gdzie nas spotkasz.', 'messenBtn' => 'Zobacz terminy'],
    ][$loc];
    $stockist = ['de' => '/de/haendlerkarte/', 'en' => '/en/stockists/', 'pl' => '/pl/mapa-dystrybutorow/'][$loc];
    $messen = ['de' => '/de/messe/', 'en' => '/en/trade-fairs/', 'pl' => '/pl/targi/'][$loc];
@endphp

@extends('layouts.eva')

@section('title', 'EvaStone — '.$t['schmuck'])

@section('content')
{{-- EKRAN 1 — hero --}}
<section class="eva-hero eva-spot">
  <img class="eva-hero__img" src="/img/photo/hero-partner-ring-am-fels-hoch-1280.webp" alt="EvaStone" fetchpriority="high">
  <div class="eva-hero__scrim" aria-hidden="true"></div>
  <div class="siatka eva-hero__in">
    <div class="col-span-12">
      <p class="eva-hero__kick" data-reveal>{{ $t['kicker'] }}</p>
      <h1 class="eva-hero__claim" data-reveal>{{ $t['claim'] }}</h1>
      <div class="eva-hero__cta" data-reveal>
        <a href="{{ $stockist }}" class="btn btn-kontra">{{ $t['stores'] }}</a>
        <a href="/{{ $loc }}/wholesale/" class="btn btn-kontra-drugi">{{ $t['b2b'] }}</a>
      </div>
    </div>
  </div>
  <div class="eva-hero__scroll" aria-hidden="true">{{ $t['scroll'] }}<span></span></div>
</section>

{{-- EKRAN 2 — kategorie na białym polu (produkty pływają) --}}
<section class="eva-sheet">
  <div class="siatka">
    <div class="col-span-12 eva-sheet-in">
      <header style="margin-bottom:3rem;max-width:40rem" data-reveal>
        <p class="eva-kick">{{ $t['schmuck'] }}</p>
        <p class="eva-lead" style="margin-top:1rem">{{ $t['schmuckLead'] }}</p>
      </header>
      <ul class="eva-grid">
        @foreach ($tiles as $tile)
          <li data-reveal>
            <a href="/{{ $loc }}/{{ $sec }}/{{ $tile['slug'] }}/" class="eva-tile" data-tilt="4">
              @if ($tile['image'])
                <img class="eva-tile__img" src="{{ $tile['image'] }}" alt="{{ $tile['name'] }}" loading="lazy" decoding="async">
              @else
                <span class="eva-tile__img" style="display:block;background:var(--paper)"></span>
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
<div class="siatka">
  <section class="col-span-12 eva-row eva-row--flip">
    <div class="eva-row__media" data-reveal>
      <img src="/img/photo/werkstatt-raum-1440.webp" alt="{{ $t['atK'] }}" loading="lazy" decoding="async">
    </div>
    <div data-reveal>
      <p class="eva-kick">{{ $t['atK'] }}</p>
      <h2 class="eva-row__title">{{ $t['atH'] }}</h2>
      <p class="eva-row__body">{{ $t['atBody'] }}</p>
      <div style="margin-top:2.25rem"><a href="/{{ $loc }}/atelier/" class="btn btn-drugi">{{ $t['atBtn'] }}</a></div>
    </div>
  </section>
</div>

{{-- EKRAN 3 — mapa sklepów (ciemny pas + spotlight) --}}
<section class="eva-quote eva-spot">
  <div class="siatka" style="align-items:center;row-gap:2.5rem">
    <div class="col-span-12 md:col-span-5" data-reveal>
      <p class="eva-kick" style="color:var(--gold-warm)">{{ $t['mapK'] }}</p>
      <h2 class="eva-quote__text" style="font-size:clamp(1.7rem,1.1rem+2.4vw,2.8rem);margin-top:1.25rem">{{ $t['mapH'] }}</h2>
      <p style="font-family:var(--sans);color:rgba(244,239,230,.72);line-height:1.65;margin:1.5rem 0 0;max-width:38ch">{{ $t['mapBody'] }}</p>
      <div style="margin-top:2.25rem"><a href="{{ $stockist }}" class="btn btn-kontra">{{ $t['mapBtn'] }}</a></div>
    </div>
    <div class="col-span-12 md:col-span-6 md:col-start-7" data-reveal>
      <a href="{{ $stockist }}" aria-label="{{ $t['mapBtn'] }}" data-tilt="3" style="display:block;aspect-ratio:16/11;position:relative;overflow:hidden;border:1px solid rgba(212,169,90,.22)">
        <img src="/img/photo/werkstatt-werkbank-1440.webp" alt="" style="width:100%;height:100%;object-fit:cover;opacity:.55" loading="lazy">
        <span style="position:absolute;inset:0;display:grid;place-items:center;color:var(--gold-warm)">
          <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
        </span>
      </a>
    </div>
  </div>
</section>

{{-- EKRAN 4 — Messen --}}
<div class="siatka">
  <section class="col-span-12 eva-cta" data-reveal>
    <div>
      <p class="eva-kick">{{ $t['messenK'] }}</p>
      <h2 class="eva-cta__h">{{ $t['messenH'] }}</h2>
    </div>
    <div class="eva-cta__btns">
      <a href="{{ $messen }}" class="btn btn-drugi">{{ $t['messenBtn'] }}</a>
    </div>
  </section>
</div>
@endsection
