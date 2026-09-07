@extends('layouts.eva')
@section('title', $title.' — EvaStone')
@section('meta', $lead)

@section('content')

<section class="siatka eva-head">
  <div class="col-span-12 md:col-span-6">
    <p class="eva-kick">{{ $kick }}</p>
    <h1 class="eva-head__title">{{ $title }}</h1>
    <p class="eva-lead">{{ $lead }}</p>
  </div>
  <div class="col-span-12 md:col-span-5 md:col-start-8" style="align-self:end">
    <img src="/img/photo/struktur-makro-kette-auf-stein-1440.webp" alt="{{ $kick }}" style="width:100%;aspect-ratio:4/3;object-fit:cover;display:block" fetchpriority="high">
  </div>
</section>

{{-- karty edukacyjne --}}
<section class="siatka" style="padding-bottom:1rem">
  <div class="col-span-12">
    <ul class="eva-cards">
      @foreach ($cards as [$kck, $h, $b])
        <li class="eva-card">
          <p class="eva-card__kick">{{ $kck }}</p>
          <h2 class="eva-card__h">{{ $h }}</h2>
          <p class="eva-card__b">{{ $b }}</p>
        </li>
      @endforeach
    </ul>
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
      <a href="{{ $lGallery }}" class="btn btn-glowny">{{ $ctaGallery }}</a>
      <a href="{{ $lStore }}" class="btn btn-drugi">{{ $ctaStore }}</a>
    </div>
  </section>
</div>

@endsection
