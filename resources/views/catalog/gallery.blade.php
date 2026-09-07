@php
    $loc = $locale;
    $section = $section ?? config("evastone.catalog_sections.{$loc}");
    $category = $category ?? null;
    $sec = ['de' => 'schmuck', 'en' => 'jewellery', 'pl' => 'bizuteria'][$loc] ?? 'schmuck';
    $w = [
        'de' => ['kicker' => 'Kollektion', 'all' => 'Schmuck', 'lead' => 'Eine Auswahl aus der eigenen Fertigung. Verfügbarkeit über Ihre Verkaufsstelle.', 'b2b' => 'Im B2B-Portal ansehen', 'avail' => 'Erhältlich über unabhängige Boutiquen — fragen Sie in Ihrer Verkaufsstelle.', 'find' => 'Verkaufsstelle finden', 'close' => 'Schließen', 'stockist' => '/de/haendlerkarte/'],
        'en' => ['kicker' => 'Collection', 'all' => 'Jewellery', 'lead' => 'A selection from our own workshop. Availability through your local stockist.', 'b2b' => 'View in the B2B portal', 'avail' => 'Available through independent boutiques — ask at your local stockist.', 'find' => 'Find a stockist', 'close' => 'Close', 'stockist' => '/en/stockists/'],
        'pl' => ['kicker' => 'Kolekcja', 'all' => 'Biżuteria', 'lead' => 'Wybór z naszej pracowni. Dostępność w Twoim punkcie sprzedaży.', 'b2b' => 'Zobacz w portalu B2B', 'avail' => 'Dostępne w niezależnych butikach — zapytaj w punkcie sprzedaży.', 'find' => 'Znajdź punkt sprzedaży', 'close' => 'Zamknij', 'stockist' => '/pl/mapa-dystrybutorow/'],
    ][$loc];
    $h1 = $category ? ($category->translation($loc)?->name ?? $w['all']) : $w['all'];
@endphp

@extends('layouts.eva')

@section('title', $h1.' — EvaStone')

@section('content')
<section x-data="{ lb: null }" @keydown.escape.window="lb = null">

  <nav aria-label="Breadcrumb" class="siatka pt-6">
    <ol class="col-span-12 etykieta text-szept" style="display:flex;gap:.5rem;align-items:center;list-style:none;margin:0;padding:0">
      <li><a href="/{{ $loc }}/" class="link-nav text-szept">EvaStone</a></li>
      <li aria-hidden="true" style="opacity:.5">/</li>
      @if ($category)
        <li><a href="/{{ $loc }}/{{ $sec }}/" class="link-nav text-szept">{{ $w['all'] }}</a></li>
        <li aria-hidden="true" style="opacity:.5">/</li>
        <li class="text-tusz" aria-current="page">{{ $h1 }}</li>
      @else
        <li class="text-tusz" aria-current="page">{{ $w['kicker'] }}</li>
      @endif
    </ol>
  </nav>

  <header class="siatka pt-10 pb-8 md:pt-14 md:pb-10">
    <div class="col-span-12" style="max-width:44rem">
      <p class="eva-kick">{{ $w['kicker'] }}</p>
      <h1 class="text-mega font-display" style="margin:1.25rem 0 0;text-wrap:balance">{{ $h1 }}</h1>
      <p class="text-body text-glos" style="margin-top:1.25rem">{{ $w['lead'] }}</p>
    </div>
  </header>

  {{-- filtry kategorii + wejście do B2B --}}
  <div class="siatka" style="margin-bottom:.5rem">
    <div class="col-span-12" style="display:flex;flex-wrap:wrap;gap:.6rem;align-items:center;border-top:1px solid var(--eva-linia);border-bottom:1px solid var(--eva-linia);padding:1.25rem 0">
      <a href="/{{ $loc }}/{{ $sec }}/" class="btn {{ $category ? 'btn-drugi' : 'btn-glowny' }}">{{ $w['all'] }}</a>
      @foreach ($filterCats as $c)
        @php $cs = $c->translation($loc)?->slug_localized; @endphp
        @if ($cs)
          <a href="/{{ $loc }}/{{ $sec }}/{{ $cs }}/" class="btn {{ $category && $category->id === $c->id ? 'btn-glowny' : 'btn-drugi' }}">{{ $c->translation($loc)?->name }}</a>
        @endif
      @endforeach
      <a href="/{{ $loc }}/wholesale/" class="btn btn-drugi" style="margin-left:auto">{{ $w['b2b'] }}</a>
    </div>
  </div>

  {{-- białe pole: packshoty pływają, bez ramek/pudełek --}}
  <section class="eva-sheet" aria-label="{{ $h1 }}">
    <div class="siatka">
      <div class="col-span-12 eva-sheet-in">
        <ul class="eva-grid">
          @foreach ($products as $product)
            @php $img = $product->getFirstMedia('gallery')?->getUrl(); $alt = $product->category?->translation($loc)?->name ?? 'EvaStone'; @endphp
            @if ($img)
              <li>
                <button type="button" class="eva-shot" @click="lb = '{{ $img }}'" aria-label="{{ $alt }}">
                  <img class="eva-shot__img" src="{{ $img }}" alt="{{ $alt }}" width="1080" height="1080" loading="lazy" decoding="async">
                  <span class="eva-zoom" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3M11 8v6M8 11h6"/></svg></span>
                </button>
              </li>
            @endif
          @endforeach
        </ul>
      </div>
    </div>
  </section>

  {{-- lightbox: oprawione zdjęcie + wyjścia (mapa dilerów / B2B) --}}
  <div class="eva-lb" x-show="lb" x-cloak style="display:none" @click.self="lb = null" x-transition.opacity.duration.200ms>
    <div class="eva-lb__panel" @click.stop>
      <button type="button" class="eva-lb__x" @click="lb = null" aria-label="{{ $w['close'] }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 5l14 14M19 5L5 19"/></svg>
      </button>
      <div class="eva-lb__frame"><img :src="lb" alt=""></div>
      <div class="eva-lb__foot">
        <p class="etykieta text-glos" style="margin:0;max-width:24ch">{{ $w['avail'] }}</p>
        <div style="display:flex;flex-wrap:wrap;gap:.75rem;flex-shrink:0">
          <a href="{{ $w['stockist'] }}" class="btn btn-glowny">{{ $w['find'] }}</a>
          <a href="/{{ $loc }}/wholesale/" class="btn btn-drugi">{{ $w['b2b'] }}</a>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
