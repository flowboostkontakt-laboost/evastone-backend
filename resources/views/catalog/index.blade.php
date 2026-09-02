@php
    $loc = $locale;
    $section = $section ?? config("evastone.catalog_sections.{$loc}");
    $category = $category ?? null;
    $finishLabels = [
        'de' => ['silber' => '925 Silber', 'vergoldet' => '925 Silber mit Vergoldung', 'oxidiert' => '925 Silber, oxidiert', 'kombi' => '925 Silber, Vergoldung und Oxidierung'],
        'en' => ['silber' => '925 silver', 'vergoldet' => '925 silver, gilded', 'oxidiert' => '925 silver, oxidised', 'kombi' => '925 silver, gilded and oxidised'],
        'pl' => ['silber' => 'srebro 925', 'vergoldet' => 'srebro 925 z pozłotą', 'oxidiert' => 'srebro 925, oksydowane', 'kombi' => 'srebro 925, pozłota i oksyda'],
    ][$loc] ?? [];
    $words = ['de' => ['kicker' => 'Katalog', 'all' => 'Alle Modelle', 'count' => 'Modelle', 'cats' => 'Kategorien'], 'en' => ['kicker' => 'Catalogue', 'all' => 'All models', 'count' => 'models', 'cats' => 'Categories'], 'pl' => ['kicker' => 'Katalog', 'all' => 'Wszystkie modele', 'count' => 'modeli', 'cats' => 'Kategorie']][$loc] ?? [];
    $h1 = $category ? ($category->translation($loc)?->name ?? $words['all']) : $words['all'];
    // wszystkie kategorie z produktami (do nawigacji)
    $allCats = \App\Domain\Catalog\Models\Category::whereHas('products', fn ($q) => $q->where('status', 'published'))->with('translations')->orderBy('position')->get();
@endphp

@extends('layouts.eva')

@section('title', $h1.' — EvaStone')

@section('content')
<nav aria-label="Breadcrumb" class="siatka pt-6">
  <ol class="col-span-12 flex flex-nowrap items-center gap-x-2 etykieta text-szept list-none p-0 m-0 overflow-x-auto whitespace-nowrap min-h-6">
    <li class="flex items-center gap-2"><a href="/{{ $loc }}/" class="link-nav text-szept hover:text-tusz">EvaStone</a><span aria-hidden="true" class="opacity-50">/</span></li>
    @if ($category)
      <li class="flex items-center gap-2"><a href="/{{ $loc }}/{{ $section }}/" class="link-nav text-szept hover:text-tusz">{{ $words['kicker'] }}</a><span aria-hidden="true" class="opacity-50">/</span></li>
      <li class="flex items-center gap-2"><span class="text-tusz" aria-current="page">{{ $h1 }}</span></li>
    @else
      <li class="flex items-center gap-2"><span class="text-tusz" aria-current="page">{{ $words['kicker'] }}</span></li>
    @endif
  </ol>
</nav>

<section class="pt-12 pb-6 md:pt-16 md:pb-8">
  <div class="siatka">
    <header class="col-span-12">
      <p class="etykieta punca text-glos">{{ $words['kicker'] }}</p>
      <h1 class="text-mega [hyphens:auto] mt-5 text-balance">{{ $h1 }}</h1>
      <p class="etykieta text-szept mt-5"><span class="liczba">{{ number_format($products->count(), 0, ',', ' ') }}</span> {{ $words['count'] }}</p>
    </header>
  </div>
</section>

<section class="pb-8">
  <div class="siatka">
    <div class="col-span-12 flex flex-wrap gap-3 border-y border-linia py-5">
      <a href="/{{ $loc }}/{{ $section }}/" class="btn {{ $category ? 'btn-drugi' : 'btn-glowny' }}">{{ $words['all'] }}</a>
      @foreach ($allCats as $c)
        @php $cs = $c->translation($loc)?->slug_localized; @endphp
        @if ($cs)
          <a href="/{{ $loc }}/{{ $section }}/{{ $cs }}/" class="btn {{ $category && $category->id === $c->id ? 'btn-glowny' : 'btn-drugi' }}">{{ $c->translation($loc)?->name }}</a>
        @endif
      @endforeach
    </div>
  </div>
</section>

<section class="pb-16 md:pb-24" aria-label="{{ $h1 }}">
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
                <img src="{{ $img }}" alt="{{ $catName }} {{ $product->model_no }}" class="w-full aspect-[4/5] object-cover" width="1080" height="1350" loading="lazy" decoding="async">
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
