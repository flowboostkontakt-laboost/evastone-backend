@php
    $loc = $locale;
    $section = $section ?? config("evastone.catalog_sections.{$loc}");
    $category = $category ?? null;
    $w = [
        'de' => ['kicker' => 'Kollektion', 'all' => 'Schmuck', 'count' => 'Modelle', 'b2b' => 'Im B2B-Portal ansehen', 'lead' => 'Eine Auswahl aus der Fertigung. Verfügbarkeit über Ihre Verkaufsstelle.'],
        'en' => ['kicker' => 'Collection', 'all' => 'Jewellery', 'count' => 'models', 'b2b' => 'View in the B2B portal', 'lead' => 'A selection from production. Availability through your local stockist.'],
        'pl' => ['kicker' => 'Kolekcja', 'all' => 'Biżuteria', 'count' => 'modeli', 'b2b' => 'Zobacz w portalu B2B', 'lead' => 'Wybór z produkcji. Dostępność w Twoim punkcie sprzedaży.'],
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

  <section class="pb-16 md:pb-24" aria-label="{{ $h1 }}">
    <div class="siatka">
      <ul class="col-span-12 grid grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6 list-none p-0">
        @foreach ($products as $product)
          @php $img = $product->getFirstMedia('gallery')?->getUrl(); @endphp
          @if ($img)
            <li class="grupa-kadr">
              <button type="button" @click="lb = '{{ $img }}'"
                      class="block w-full p-0 border-0 bg-plotno cursor-zoom-in"
                      aria-label="{{ $product->category?->translation($loc)?->name ?? 'EvaStone' }}">
                <img src="{{ $img }}" alt="{{ $product->category?->translation($loc)?->name ?? 'EvaStone' }}"
                     class="w-full aspect-[4/5] object-cover" width="1080" height="1350" loading="lazy" decoding="async">
              </button>
            </li>
          @endif
        @endforeach
      </ul>
    </div>
  </section>

  {{-- lightbox --}}
  <div x-show="lb" x-cloak style="display:none" @click="lb = null"
       class="fixed inset-0 z-50 bg-tusz/90 flex items-center justify-center p-4 cursor-zoom-out"
       x-transition.opacity>
    <img :src="lb" alt="" class="max-h-[90vh] max-w-[90vw] object-contain">
    <button type="button" @click="lb = null" class="absolute top-5 right-5 text-papier/80 hover:text-papier bg-transparent border-0 cursor-pointer" aria-label="close">
      <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 5l14 14M19 5L5 19"/></svg>
    </button>
  </div>
</section>
@endsection
