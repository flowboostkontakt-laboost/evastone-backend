@php
    $loc = $locale;
    $section = $section ?? config("evastone.catalog_sections.{$loc}");
    $finishLabels = [
        'de' => ['silber' => '925 Silber', 'vergoldet' => '925 Silber mit Vergoldung', 'oxidiert' => '925 Silber, oxidiert', 'kombi' => '925 Silber, Vergoldung und Oxidierung'],
        'en' => ['silber' => '925 silver', 'vergoldet' => '925 silver, gilded', 'oxidiert' => '925 silver, oxidised', 'kombi' => '925 silver, gilded and oxidised'],
        'pl' => ['silber' => 'srebro 925', 'vergoldet' => 'srebro 925 z pozłotą', 'oxidiert' => 'srebro 925, oksydowane', 'kombi' => 'srebro 925, pozłota i oksyda'],
    ][$loc] ?? [];
    $head = ['de' => ['kicker' => 'Katalog', 'h1' => 'Alle Modelle', 'count' => 'Modelle'], 'en' => ['kicker' => 'Catalogue', 'h1' => 'All models', 'count' => 'models'], 'pl' => ['kicker' => 'Katalog', 'h1' => 'Wszystkie modele', 'count' => 'modeli']][$loc] ?? [];
@endphp

@extends('layouts.eva')

@section('title', ($head['h1'] ?? 'Katalog').' — EvaStone')

@section('content')
<section class="pt-12 pb-8 md:pt-16 md:pb-10">
  <div class="siatka">
    <header class="col-span-12">
      <p class="etykieta punca text-glos">{{ $head['kicker'] ?? 'Katalog' }}</p>
      <h1 class="text-mega [hyphens:auto] mt-5 text-balance">{{ $head['h1'] ?? 'Katalog' }}</h1>
      <p class="etykieta text-szept mt-5"><span class="liczba">{{ number_format($products->count(), 0, ',', ' ') }}</span> {{ $head['count'] ?? '' }}</p>
    </header>
  </div>
</section>

<section class="pb-16 md:pb-24" aria-label="{{ $head['h1'] ?? 'Katalog' }}">
  <div class="siatka">
    <ul id="siatka-produktow" class="col-span-12 grid grid-cols-2 lg:grid-cols-3 gap-x-6 lg:gap-x-10 gap-y-14 list-none p-0">
      @foreach ($products as $product)
        @php
          $cat = $product->category;
          $catName = $cat?->translation($loc)?->name ?? '';
          $catSlug = $cat?->translation($loc)?->slug_localized;
          $prodSlug = $product->translation($loc)?->slug_localized;
          $stoneName = $product->stone?->translation($loc)?->name;
          $finish = $finishLabels[$product->spec['finish'] ?? 'silber'] ?? '925 Silber';
          $img = $product->getFirstMedia('gallery')?->getUrl();
          $url = $catSlug && $prodSlug ? "/{$loc}/{$section}/{$catSlug}/{$prodSlug}/" : null;
          $spec = trim(($stoneName ? $stoneName.' · ' : '').$finish, ' ·');
        @endphp
        <li class="grupa-kadr">
          <a @if($url) href="{{ $url }}" @endif class="group block no-underline text-tusz">
            <div class="kadr bg-plotno">
              @if ($img)
                <img src="{{ $img }}" alt="{{ $catName }} {{ $product->model_no }}"
                     class="w-full aspect-[4/5] object-cover" width="1080" height="1350"
                     loading="lazy" decoding="async">
              @else
                <div class="w-full aspect-[4/5]"></div>
              @endif
            </div>
            <div class="mt-4 flex items-baseline justify-between gap-4 border-b border-linia pb-3">
              <p class="font-display text-h2 leading-tight m-0">{{ $catName }}</p>
              <p class="numer-sekcji liczba text-szept m-0 shrink-0">{{ $product->model_no }}</p>
            </div>
            <p class="text-caption text-glos mt-2 m-0 group-hover:text-tusz transition-colors">{{ $spec }}</p>
          </a>
        </li>
      @endforeach
    </ul>
  </div>
</section>
@endsection
