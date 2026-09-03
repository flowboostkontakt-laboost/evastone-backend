@php
    $loc = $locale;
    $t = [
        'de' => ['h1' => 'Danke — wir haben Ihre Anfrage.', 'lead' => 'Eine Person meldet sich innerhalb von 2 Werktagen. Keine Automation.', 'back' => 'Zurück zum Katalog'],
        'en' => ['h1' => 'Thank you — we received your request.', 'lead' => 'A person will get back to you within 2 working days. No automation.', 'back' => 'Back to the catalogue'],
        'pl' => ['h1' => 'Dziękujemy — mamy Twoje zapytanie.', 'lead' => 'Odezwie się człowiek w ciągu 2 dni roboczych. Bez automatów.', 'back' => 'Wróć do katalogu'],
    ][$loc] ?? [];
    $section = config("evastone.catalog_sections.{$loc}");
@endphp

@extends('layouts.eva')

@section('title', ($t['h1'] ?? 'Danke').' — EvaStone')
@section('head')<meta name="robots" content="noindex, follow">@endsection

@section('content')
<section class="siatka py-24 md:py-32">
  <div class="col-span-12 md:col-span-8">
    <p class="etykieta punca text-glos">EvaStone</p>
    <h1 class="text-mega [hyphens:auto] mt-5 text-balance">{{ $t['h1'] }}</h1>
    <p class="text-body mt-6 miara text-tusz/85">{{ $t['lead'] }}</p>
    <div class="mt-10">
      <a href="/{{ $loc }}/{{ $section }}/" class="btn btn-glowny">{{ $t['back'] }}</a>
    </div>
  </div>
</section>
@endsection
