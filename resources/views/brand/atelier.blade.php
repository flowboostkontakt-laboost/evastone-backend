@extends('layouts.eva')
@section('title', $title.' — EvaStone')
@section('meta', $lead)

@section('content')

{{-- nagłówek + zdjęcie pracowni --}}
<section class="siatka eva-head">
  <div class="col-span-12 md:col-span-6">
    <p class="eva-kick">{{ $kick }}</p>
    <h1 class="eva-head__title">{{ $title }}</h1>
    <p class="eva-lead">{{ $lead }}</p>
  </div>
  <div class="col-span-12 md:col-span-5 md:col-start-8" style="align-self:end">
    <img src="/img/photo/werkstatt-raum-1440.webp" alt="{{ $kick }}" style="width:100%;aspect-ratio:4/3;object-fit:cover;display:block" fetchpriority="high">
  </div>
</section>

{{-- cytat --}}
<section class="eva-quote">
  <div class="siatka">
    <blockquote class="col-span-12 md:col-span-9" style="margin:0">
      <span class="eva-quote__mark" aria-hidden="true">“</span>
      <p class="eva-quote__text">{{ $quote }}</p>
    </blockquote>
  </div>
</section>

{{-- rzemiosło: obraz ↔ tekst --}}
<div class="siatka">
  <section class="col-span-12 eva-row eva-row--flip">
    <div class="eva-row__media"><img src="/img/photo/werkstatt-werkbank-1440.webp" alt="{{ $craftKick }}" loading="lazy"></div>
    <div>
      <p class="eva-kick">{{ $craftKick }}</p>
      <h2 class="eva-row__title">{{ $craftTitle }}</h2>
      <p class="eva-row__body">{{ $craftBody }}</p>
    </div>
  </section>
</div>

{{-- oś czasu --}}
<section class="siatka" style="padding-block:1rem 3.5rem">
  <div class="col-span-12 md:col-span-10">
    <p class="eva-kick">{{ $timelineKick }}</p>
    <h2 class="eva-head__title" style="font-size:clamp(1.8rem,1.2rem+2.4vw,2.8rem)">{{ $timelineTitle }}</h2>
    <ul class="eva-time">
      @foreach ($timeline as [$year, $h, $b])
        <li>
          <span class="eva-time__year">{{ $year }}</span>
          <div><p class="eva-time__h">{{ $h }}</p><p class="eva-time__b">{{ $b }}</p></div>
        </li>
      @endforeach
    </ul>
  </div>
</section>

{{-- różnica pod palcem: porównanie zdjęć --}}
<section class="eva-sheet">
  <div class="siatka">
    <div class="col-span-12 eva-sheet-in">
      <div style="max-width:44rem">
        <p class="eva-kick">{{ $diffKick }}</p>
        <h2 class="eva-row__title" style="margin-top:.75rem">{{ $diffTitle }}</h2>
        <p class="eva-row__body">{{ $diffBody }}</p>
      </div>
      <div class="eva-pair">
        <figure><img src="/img/photo/silber-glatte-schiene-1440.webp" alt="{{ $diffCapA }}" loading="lazy"><figcaption>{{ $diffCapA }}</figcaption></figure>
        <figure><img src="/img/photo/silber-oberflaechenstruktur-1440.webp" alt="{{ $diffCapB }}" loading="lazy"><figcaption>{{ $diffCapB }}</figcaption></figure>
      </div>
    </div>
  </div>
</section>

{{-- CTA --}}
<div class="siatka">
  <section class="col-span-12 eva-cta">
    <div>
      <p class="eva-kick">{{ $ctaBody }}</p>
      <h2 class="eva-cta__h">{{ $ctaTitle }}</h2>
    </div>
    <div class="eva-cta__btns">
      <a href="{{ $lStore }}" class="btn btn-glowny">{{ $ctaStore }}</a>
      <a href="{{ $lB2b }}" class="btn btn-drugi">{{ $ctaB2b }}</a>
    </div>
  </section>
</div>

@endsection
