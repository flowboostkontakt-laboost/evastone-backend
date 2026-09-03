@php
    $loc = $locale;
    $section = $section ?? config("evastone.catalog_sections.{$loc}");
    $category = $category ?? null;
    $page = $products->currentPage();
    $sections = config('evastone.catalog_sections');
    $finishLabels = [
        'de' => ['silber' => '925 Silber', 'vergoldet' => '925 Silber mit Vergoldung', 'oxidiert' => '925 Silber, oxidiert', 'kombi' => '925 Silber, Vergoldung und Oxidierung'],
        'en' => ['silber' => '925 silver', 'vergoldet' => '925 silver, gilded', 'oxidiert' => '925 silver, oxidised', 'kombi' => '925 silver, gilded and oxidised'],
        'pl' => ['silber' => 'srebro 925', 'vergoldet' => 'srebro 925 z pozłotą', 'oxidiert' => 'srebro 925, oksydowane', 'kombi' => 'srebro 925, pozłota i oksyda'],
    ][$loc] ?? [];
    $w = ['de' => ['kicker' => 'Katalog', 'all' => 'Alle Modelle', 'count' => 'Modelle', 'page' => 'Seite', 'prev' => 'Zurück', 'next' => 'Weiter'], 'en' => ['kicker' => 'Catalogue', 'all' => 'All models', 'count' => 'models', 'page' => 'Page', 'prev' => 'Previous', 'next' => 'Next'], 'pl' => ['kicker' => 'Katalog', 'all' => 'Wszystkie modele', 'count' => 'modeli', 'page' => 'Strona', 'prev' => 'Wstecz', 'next' => 'Dalej']][$loc] ?? [];
    $h1 = $category ? ($category->translation($loc)?->name ?? $w['all']) : $w['all'];
    $allCats = \App\Domain\Catalog\Models\Category::whereHas('products', fn ($q) => $q->where('status', 'published'))->with('translations')->orderBy('position')->get();
    // adresy per język (hreflang) — z zachowaniem numeru strony
    $q = $page > 1 ? '?page='.$page : '';
    $alt = [];
    foreach (['de', 'en', 'pl'] as $al) {
        $as = $sections[$al];
        if ($category) {
            $acs = $category->translation($al)?->slug_localized;
            if ($acs) $alt[$al] = url("/{$al}/{$as}/{$acs}/").$q;
        } else {
            $alt[$al] = url("/{$al}/{$as}/").$q;
        }
    }
    $titleBase = $h1.($page > 1 ? ' — '.$w['page'].' '.$page : '');
@endphp

@extends('layouts.eva')

@section('title', $titleBase.' — EvaStone')

@section('head')
    @foreach ($alt as $al => $u)<link rel="alternate" hreflang="{{ $al }}" href="{{ $u }}">
    @endforeach
    @if (isset($alt[config('evastone.x_default')]))<link rel="alternate" hreflang="x-default" href="{{ $alt[config('evastone.x_default')] }}">@endif
    @if ($products->previousPageUrl())<link rel="prev" href="{{ $products->previousPageUrl() }}">@endif
    @if ($products->nextPageUrl())<link rel="next" href="{{ $products->nextPageUrl() }}">@endif
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            ['@type' => 'CollectionPage', 'name' => $h1, 'url' => url()->current()],
            ['@type' => 'ItemList', 'numberOfItems' => $products->total(), 'itemListElement' => $products->map(fn ($p, $i) => [
                '@type' => 'ListItem', 'position' => ($products->firstItem() ?? 0) + $i,
                'name' => trim(($p->category?->translation($loc)?->name ?? '').' '.$p->model_no),
            ])->values()->all()],
            ['@type' => 'BreadcrumbList', 'itemListElement' => array_values(array_filter([
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'EvaStone', 'item' => url("/{$loc}/")],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $w['kicker'], 'item' => url("/{$loc}/{$section}/")],
                $category ? ['@type' => 'ListItem', 'position' => 3, 'name' => $h1, 'item' => url()->current()] : null,
            ]))],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('content')
<nav aria-label="Breadcrumb" class="siatka pt-6">
  <ol class="col-span-12 flex flex-nowrap items-center gap-x-2 etykieta text-szept list-none p-0 m-0 overflow-x-auto whitespace-nowrap min-h-6">
    <li class="flex items-center gap-2"><a href="/{{ $loc }}/" class="link-nav text-szept hover:text-tusz">EvaStone</a><span aria-hidden="true" class="opacity-50">/</span></li>
    @if ($category)
      <li class="flex items-center gap-2"><a href="/{{ $loc }}/{{ $section }}/" class="link-nav text-szept hover:text-tusz">{{ $w['kicker'] }}</a><span aria-hidden="true" class="opacity-50">/</span></li>
      <li class="flex items-center gap-2"><span class="text-tusz" aria-current="page">{{ $h1 }}</span></li>
    @else
      <li class="flex items-center gap-2"><span class="text-tusz" aria-current="page">{{ $w['kicker'] }}</span></li>
    @endif
  </ol>
</nav>

<section class="pt-12 pb-6 md:pt-16 md:pb-8">
  <div class="siatka">
    <header class="col-span-12">
      <p class="etykieta punca text-glos">{{ $w['kicker'] }}</p>
      <h1 class="text-mega [hyphens:auto] mt-5 text-balance">{{ $h1 }}</h1>
      <p class="etykieta text-szept mt-5"><span class="liczba">{{ number_format($products->total(), 0, ',', ' ') }}</span> {{ $w['count'] }}@if ($products->lastPage() > 1) · {{ $w['page'] }} {{ $page }}/{{ $products->lastPage() }}@endif</p>
    </header>
  </div>
</section>

<section class="pb-8">
  <div class="siatka">
    <div class="col-span-12 flex flex-wrap gap-3 border-y border-linia py-5">
      <a href="/{{ $loc }}/{{ $section }}/" class="btn {{ $category ? 'btn-drugi' : 'btn-glowny' }}">{{ $w['all'] }}</a>
      @foreach ($allCats as $c)
        @php $cs = $c->translation($loc)?->slug_localized; @endphp
        @if ($cs)
          <a href="/{{ $loc }}/{{ $section }}/{{ $cs }}/" class="btn {{ $category && $category->id === $c->id ? 'btn-glowny' : 'btn-drugi' }}">{{ $c->translation($loc)?->name }}</a>
        @endif
      @endforeach
    </div>
  </div>
</section>

<section class="pb-12 md:pb-16" aria-label="{{ $h1 }}">
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

    @if ($products->lastPage() > 1)
      <nav class="col-span-12 mt-14 flex items-center justify-between gap-4 border-t border-linia pt-6" aria-label="{{ $w['page'] }}">
        <div>
          @if ($products->previousPageUrl())
            <a href="{{ $products->previousPageUrl() }}" class="btn btn-drugi" rel="prev">← {{ $w['prev'] }}</a>
          @endif
        </div>
        <p class="etykieta text-szept m-0">{{ $w['page'] }} <span class="liczba">{{ $page }}</span> / {{ $products->lastPage() }}</p>
        <div>
          @if ($products->nextPageUrl())
            <a href="{{ $products->nextPageUrl() }}" class="btn btn-drugi" rel="next">{{ $w['next'] }} →</a>
          @endif
        </div>
      </nav>
    @endif
  </div>
</section>
@endsection
