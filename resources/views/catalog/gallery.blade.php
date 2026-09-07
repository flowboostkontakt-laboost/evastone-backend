@php
    $loc = $locale;
    $section = $section ?? config("evastone.catalog_sections.{$loc}");
    $category = $category ?? null;
    $w = [
        'de' => ['kicker' => 'Kollektion', 'all' => 'Schmuck', 'count' => 'Modelle', 'b2b' => 'Im B2B-Portal ansehen', 'lead' => 'Eine Auswahl aus der Fertigung. Verfügbarkeit über Ihre Verkaufsstelle.', 'find' => 'Verkaufsstelle finden', 'avail' => 'Dieses Stück fragen Sie in Ihrer Verkaufsstelle nach.', 'stockist' => '/de/haendlerkarte/', 'close' => 'Schließen'],
        'en' => ['kicker' => 'Collection', 'all' => 'Jewellery', 'count' => 'models', 'b2b' => 'View in the B2B portal', 'lead' => 'A selection from production. Availability through your local stockist.', 'find' => 'Find a stockist', 'avail' => 'Ask for this piece at your local stockist.', 'stockist' => '/en/stockists/', 'close' => 'Close'],
        'pl' => ['kicker' => 'Kolekcja', 'all' => 'Biżuteria', 'count' => 'modeli', 'b2b' => 'Zobacz w portalu B2B', 'lead' => 'Wybór z produkcji. Dostępność w Twoim punkcie sprzedaży.', 'find' => 'Znajdź punkt sprzedaży', 'avail' => 'O ten egzemplarz zapytaj w punkcie sprzedaży.', 'stockist' => '/pl/mapa-dystrybutorow/', 'close' => 'Zamknij'],
    ][$loc] ?? [];
    $h1 = $category ? ($category->translation($loc)?->name ?? $w['all']) : $w['all'];
@endphp

@extends('layouts.eva')

@section('title', $h1.' — EvaStone')

@section('content')
<section x-data="{ lb: null }" @keydown.escape.window="lb = null">
  <nav aria-label="Breadcrumb" class="siatka pt-6">
    <ol class="col-span-12 flex items-center gap-x-2 etykieta text-szept list-none p-0 m-0 min-h-6">
      <li class="flex items-center gap-2"><a href="/{{ $loc }}/" class="link-nav text-szept hover:text-tusz">EvaStone</a><span aria-hidden="true" class="opacity-50">/</span></li>
      @if ($category)
        <li class="flex items-center gap-2"><a href="/{{ $loc }}/{{ $section }}/" class="link-nav text-szept hover:text-tusz">{{ $w['all'] }}</a><span aria-hidden="true" class="opacity-50">/</span></li>
        <li><span class="text-tusz" aria-current="page">{{ $h1 }}</span></li>
      @else
        <li><span class="text-tusz" aria-current="page">{{ $w['all'] }}</span></li>
      @endif
    </ol>
  </nav>

  <section class="pt-12 pb-6 md:pt-16 md:pb-8">
    <div class="siatka">
      <header class="col-span-12 md:col-span-8">
        <p class="etykieta punca text-glos">{{ $w['kicker'] }}</p>
        <h1 class="text-mega [hyphens:auto] mt-5 text-balance">{{ $h1 }}</h1>
        <p class="text-body text-glos mt-5 miara">{{ $w['lead'] }}</p>
      </header>
    </div>
  </section>

  <section class="pb-8">
    <div class="siatka">
      <div class="col-span-12 flex flex-wrap items-center gap-3 border-y border-linia py-5">
        <a href="/{{ $loc }}/{{ $section }}/" class="btn {{ $category ? 'btn-drugi' : 'btn-glowny' }}">{{ $w['all'] }}</a>
        @foreach ($filterCats as $c)
          @php $cs = $c->translation($loc)?->slug_localized; @endphp
          @if ($cs)
            <a href="/{{ $loc }}/{{ $section }}/{{ $cs }}/" class="btn {{ $category && $category->id === $c->id ? 'btn-glowny' : 'btn-drugi' }}">{{ $c->translation($loc)?->name }}</a>
          @endif
        @endforeach
        <a href="/{{ $loc }}/wholesale/" class="btn btn-drugi ml-auto">{{ $w['b2b'] }}</a>
      </div>
    </div>
  </section>

  {{-- jedno białe pole: packshoty pływają, znikają krawędzie/pudełka; dużo powietrza (minimal luxury) --}}
  <section class="bg-white border-y border-linia" aria-label="{{ $h1 }}">
    <div class="siatka py-10 md:py-16">
      <ul class="col-span-12 grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-10 md:gap-x-12 md:gap-y-16 list-none p-0 m-0">
        @foreach ($products as $product)
          @php
            $img = $product->getFirstMedia('gallery')?->getUrl();
            $alt = $product->category?->translation($loc)?->name ?? 'EvaStone';
          @endphp
          @if ($img)
            <li>
              <button type="button" @click="lb = '{{ $img }}'"
                      class="group relative block w-full cursor-zoom-in overflow-hidden"
                      aria-label="{{ $alt }}">
                <img src="{{ $img }}" alt="{{ $alt }}"
                     class="w-full aspect-square object-contain transition-transform duration-[800ms] ease-out group-hover:scale-[1.06]"
                     width="1080" height="1080" loading="lazy" decoding="async">
                <span class="absolute bottom-2 right-2 w-8 h-8 grid place-items-center text-tusz/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300" aria-hidden="true">
                  <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3M11 8v6M8 11h6"/></svg>
                </span>
              </button>
            </li>
          @endif
        @endforeach
      </ul>
    </div>
  </section>

  {{-- lightbox: oprawione zdjęcie + wyjścia (mapa dilerów / B2B) --}}
  <div x-show="lb" x-cloak style="display:none" @click.self="lb = null"
       class="fixed inset-0 z-50 bg-tusz/80 backdrop-blur-sm flex items-center justify-center p-4 md:p-8 cursor-zoom-out"
       x-transition.opacity.duration.200ms>
    <div class="bg-papier ziarno ziarno-lekkie w-full max-w-[52rem] max-h-[92vh] overflow-auto p-4 md:p-6 relative cursor-default"
         @click.stop x-transition.opacity.duration.200ms>
      <button type="button" @click="lb = null"
              class="absolute top-3 right-3 z-10 w-9 h-9 grid place-items-center bg-papier/90 text-tusz hover:text-glos border border-linia cursor-pointer"
              aria-label="{{ $w['close'] }}">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 5l14 14M19 5L5 19"/></svg>
      </button>

      <div class="bg-white p-6 md:p-10">
        <img :src="lb" alt="" class="w-full max-h-[58vh] object-contain">
      </div>

      <div class="mt-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-t border-linia pt-5">
        <p class="etykieta text-glos m-0 sm:max-w-[22ch]">{{ $w['avail'] }}</p>
        <div class="flex flex-wrap gap-3 shrink-0">
          <a href="{{ $w['stockist'] }}" class="btn btn-glowny">{{ $w['find'] }}</a>
          <a href="/{{ $loc }}/wholesale/" class="btn btn-drugi">{{ $w['b2b'] }}</a>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
