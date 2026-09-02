@php
    $loc = $locale;
    $section = config("evastone.catalog_sections.{$loc}");
    $cat = $product->category;
    $catName = $cat?->translation($loc)?->name ?? '';
    $catSlug = $cat?->translation($loc)?->slug_localized;
    $stoneName = $product->stone?->translation($loc)?->name;
    $finishLabels = [
        'de' => ['silber' => '925 Silber', 'vergoldet' => '925 Silber mit Vergoldung', 'oxidiert' => '925 Silber, oxidiert', 'kombi' => '925 Silber, Vergoldung und Oxidierung'],
        'en' => ['silber' => '925 silver', 'vergoldet' => '925 silver, gilded', 'oxidiert' => '925 silver, oxidised', 'kombi' => '925 silver, gilded and oxidised'],
        'pl' => ['silber' => 'srebro 925', 'vergoldet' => 'srebro 925 z pozłotą', 'oxidiert' => 'srebro 925, oksydowane', 'kombi' => 'srebro 925, pozłota i oksyda'],
    ][$loc] ?? [];
    $finish = $finishLabels[$product->spec['finish'] ?? 'silber'] ?? '925 Silber';
    $L = [
        'de' => ['modell'=>'Modell','spec'=>'Spezifikation','faq'=>'Fragen zu diesem Modell','nr'=>'Modellnummer','material'=>'Material','stein'=>'Edelstein','ausf'=>'Ausführung','kat'=>'Kategorie','katalog'=>'Schmuck'],
        'en' => ['modell'=>'Model','spec'=>'Specification','faq'=>'Questions about this model','nr'=>'Model number','material'=>'Material','stein'=>'Gemstone','ausf'=>'Finish','kat'=>'Category','katalog'=>'Jewellery'],
        'pl' => ['modell'=>'Model','spec'=>'Specyfikacja','faq'=>'Pytania o ten model','nr'=>'Numer modelu','material'=>'Materiał','stein'=>'Kamień','ausf'=>'Wykończenie','kat'=>'Kategoria','katalog'=>'Biżuteria'],
    ][$loc] ?? [];
    $media = $product->getFirstMedia('gallery');
    $alt = $media ? \App\Domain\Catalog\Models\MediaTranslation::where('media_id', $media->id)->where('locale', $loc)->value('alt') : null;
@endphp

@extends('layouts.eva')

@section('title', $t->meta_title ?? ($t->name.' | EvaStone'))
@section('meta', $t->meta_description ?? '')

@section('head')
    @foreach ($alternates as $altLocale => $u)
        <link rel="alternate" hreflang="{{ $altLocale }}" href="{{ $u }}">
    @endforeach
    @if (isset($alternates[config('evastone.x_default')]))
        <link rel="alternate" hreflang="x-default" href="{{ $alternates[config('evastone.x_default')] }}">
    @endif
    <script type="application/ld+json">{!! $schemaJson !!}</script>
@endsection

@section('content')
<nav aria-label="Breadcrumb" class="siatka pt-6">
  <ol class="col-span-12 flex flex-nowrap items-center gap-x-2 etykieta text-szept list-none p-0 m-0 overflow-x-auto whitespace-nowrap min-h-6">
    <li class="flex items-center gap-2"><a href="/{{ $loc }}/" class="link-nav text-szept hover:text-tusz">EvaStone</a><span aria-hidden="true" class="opacity-50">/</span></li>
    @if ($catSlug)
      <li class="flex items-center gap-2"><a href="/{{ $loc }}/{{ $section }}/{{ $catSlug }}/" class="link-nav text-szept hover:text-tusz">{{ $catName }}</a><span aria-hidden="true" class="opacity-50">/</span></li>
    @endif
    <li class="flex items-center gap-2"><span class="text-tusz" aria-current="page">{{ $product->model_no }}</span></li>
  </ol>
</nav>

<article>
  <header class="siatka pt-10 md:pt-14">
    <div class="col-span-12">
      <p class="etykieta punca text-glos">{{ $L['modell'] }} <span class="liczba">{{ $product->model_no }}</span></p>
      <h1 class="text-mega [hyphens:auto] mt-5 text-balance">{{ $t->name }}</h1>
      <p class="etykieta text-szept mt-5">{{ trim(($stoneName ? $stoneName.' · ' : '').$finish, ' ·') }}</p>
    </div>
  </header>

  <div class="siatka py-12 md:py-16 gap-y-12 items-start">
    <div class="col-span-12 lg:col-span-7">
      <figure class="m-0 grupa-kadr">
        <div class="kadr bg-plotno">
          @if ($media)
            <img src="{{ $media->getUrl() }}" alt="{{ $alt ?? ($t->name) }}"
                 class="w-full aspect-[4/5] object-cover" width="1080" height="1350" fetchpriority="high">
          @else
            <div class="w-full aspect-[4/5]"></div>
          @endif
        </div>
      </figure>

      @if (!empty($t->faq))
        <h2 class="etykieta punca text-glos mt-14">{{ $L['faq'] }}</h2>
        <div class="mt-5 border-t border-tusz">
          @foreach ($t->faq as $i => $faq)
            <details class="group border-b border-linia" @if($i === 0) open @endif>
              <summary class="flex items-start justify-between gap-6 py-4 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                <h3 class="text-h2 font-display font-normal text-balance m-0">{{ $faq['q'] }}</h3>
                <span aria-hidden="true" class="mt-2 shrink-0 relative w-4 h-4">
                  <span class="absolute left-0 top-1/2 w-4 h-px bg-tusz"></span>
                  <span class="absolute left-1/2 top-0 w-px h-4 bg-tusz transition-transform duration-200 group-open:scale-y-0"></span>
                </span>
              </summary>
              <p class="text-body pb-6 miara text-tusz/85">{{ $faq['a'] }}</p>
            </details>
          @endforeach
        </div>
      @endif
    </div>

    <aside class="col-span-12 lg:col-span-4 lg:col-start-9 lg:sticky lg:top-8">
      @if ($t->answer_summary)
        <p class="text-body miara border-t border-tusz pt-6">{{ $t->answer_summary }}</p>
      @endif
      {{-- surowy opis ComUp (HTML tabel) pomijamy w widoku — do wyczyszczenia przez klientkę --}}

      <h2 class="etykieta punca text-glos mt-10">{{ $L['spec'] }}</h2>
      <table class="w-full mt-4 text-body border-collapse">
        <tbody>
          <tr class="border-b border-linia"><th scope="row" class="text-left etykieta text-szept py-3 pr-4 align-top w-2/5">{{ $L['nr'] }}</th><td class="py-3 align-top liczba">{{ $product->model_no }}</td></tr>
          <tr class="border-b border-linia"><th scope="row" class="text-left etykieta text-szept py-3 pr-4 align-top">{{ $L['material'] }}</th><td class="py-3 align-top">925 Silber</td></tr>
          @if ($stoneName)
            <tr class="border-b border-linia"><th scope="row" class="text-left etykieta text-szept py-3 pr-4 align-top">{{ $L['stein'] }}</th><td class="py-3 align-top">{{ $stoneName }}</td></tr>
          @endif
          <tr class="border-b border-linia"><th scope="row" class="text-left etykieta text-szept py-3 pr-4 align-top">{{ $L['ausf'] }}</th><td class="py-3 align-top">{{ $finish }}</td></tr>
          <tr class="border-b border-linia"><th scope="row" class="text-left etykieta text-szept py-3 pr-4 align-top">{{ $L['kat'] }}</th><td class="py-3 align-top">{{ $catName }}</td></tr>
        </tbody>
      </table>
    </aside>
  </div>
</article>
@endsection
