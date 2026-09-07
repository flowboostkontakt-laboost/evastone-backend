@extends('layouts.eva')
@section('title', $title.' — EvaStone')
@section('meta', $lead)

@section('content')

<section class="siatka eva-head">
  <div class="col-span-12 md:col-span-8">
    <p class="eva-kick">{{ $kick }}</p>
    <h1 class="eva-head__title">{{ $title }}</h1>
    <p class="eva-lead">{{ $lead }}</p>
  </div>
</section>

{{-- mapa Google = główny element strony (plan v1.0 §8) --}}
<section class="siatka" style="padding-bottom:3.5rem;align-items:stretch;row-gap:2rem">
  <div class="col-span-12 md:col-span-7">
    <div class="eva-map">
      <iframe
        title="EvaStone — {{ $mapTitle }}"
        src="https://www.google.com/maps?q={{ $mapsQuery }}&z=12&output=embed"
        loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
    </div>
  </div>
  <div class="col-span-12 md:col-span-4 md:col-start-9" style="display:flex;flex-direction:column;justify-content:center">
    <p class="eva-kick">{{ $mapTitle }}</p>
    <address class="not-italic" style="margin:1.25rem 0 0;font-size:1.05rem;line-height:1.6;color:var(--eva-tusz)">
      {{ $addr['street'] }}<br>{{ $addr['city'] }}<br>{{ $addr['country'] }}
    </address>
    <p style="margin-top:1.25rem;line-height:1.7">
      <a href="tel:{{ $addr['phone_href'] }}" class="link-nav text-tusz">{{ $addr['phone'] }}</a><br>
      <a href="mailto:{{ $addr['email'] }}" class="link-podkreslony text-tusz">{{ $addr['email'] }}</a>
    </p>
    <p style="margin-top:1.5rem;font-size:.95rem;line-height:1.6;color:var(--eva-glos)">{{ $mapNote }}</p>
  </div>
</section>

{{-- CTA dla butików --}}
<div class="siatka">
  <section class="col-span-12 eva-cta">
    <div>
      <p class="eva-kick">{{ $boutiqueBody }}</p>
      <h2 class="eva-cta__h">{{ $boutiqueTitle }}</h2>
    </div>
    <div class="eva-cta__btns">
      <a href="{{ $lContact }}" class="btn btn-glowny">{{ $ctaContact }}</a>
      <a href="{{ $lB2b }}" class="btn btn-drugi">{{ $ctaB2b }}</a>
    </div>
  </section>
</div>

@endsection
