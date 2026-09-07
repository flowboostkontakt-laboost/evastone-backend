@extends('layouts.eva')
@section('title', 'B2B — EvaStone')
@section('meta', $lead)

@section('content')

{{-- 1 — pozycjonowanie --}}
<section class="siatka eva-head">
  <div class="col-span-12 md:col-span-8">
    <p class="eva-kick">{{ $kick }}</p>
    <h1 class="eva-head__title">{{ $title }}</h1>
    <p class="eva-lead">{{ $lead }}</p>
    <div style="display:flex;flex-wrap:wrap;gap:.75rem;margin-top:2rem">
      <a href="#partner-werden" class="btn btn-glowny">{{ $formKick }}</a>
      @if ($portal)
        <a href="{{ $portal }}" class="btn btn-drugi" rel="noopener" target="_blank">{{ $portalBtn }} ↗</a>
      @endif
    </div>
  </div>
</section>

{{-- 2 — dlaczego (różnicowanie) obraz ↔ tekst --}}
<div class="siatka">
  <section class="col-span-12 eva-row eva-row--flip">
    <div class="eva-row__media"><img src="/img/photo/silber-oberflaechenstruktur-1440.webp" alt="{{ $whyKick }}" loading="lazy"></div>
    <div>
      <p class="eva-kick">{{ $whyKick }}</p>
      <h2 class="eva-row__title">{{ $whyTitle }}</h2>
      <p class="eva-row__body">{{ $whyBody }}</p>
    </div>
  </section>
</div>

{{-- 3 — jak działa zamówienie (proces, zaufanie) --}}
<section class="eva-sheet">
  <div class="siatka">
    <div class="col-span-12 eva-sheet-in">
      <div style="max-width:44rem">
        <p class="eva-kick">{{ $howKick }}</p>
        <h2 class="eva-row__title" style="margin-top:.75rem">{{ $howTitle }}</h2>
      </div>
      <ol class="eva-steps">
        @foreach ($steps as $i => [$h, $b])
          <li>
            <span class="eva-steps__no">{{ sprintf('%02d', $i + 1) }}</span>
            <div><p class="eva-steps__h">{{ $h }}</p><p class="eva-steps__b">{{ $b }}</p></div>
          </li>
        @endforeach
      </ol>
    </div>
  </div>
</section>

{{-- 4 — warunki --}}
<section class="siatka" style="padding-block:3.5rem 1rem;align-items:start;row-gap:2rem">
  <div class="col-span-12 md:col-span-4">
    <p class="eva-kick">{{ $termsKick }}</p>
    <h2 class="eva-row__title" style="margin-top:.75rem">{{ $termsTitle }}</h2>
    <p class="eva-row__body" style="font-size:.92rem;color:var(--eva-szept)">{{ $termsNote }}</p>
  </div>
  <div class="col-span-12 md:col-span-7 md:col-start-6">
    <ul class="eva-points">
      @foreach ($terms as $t)<li>{{ $t }}</li>@endforeach
    </ul>
  </div>
</section>

{{-- 5 — pasuje / nie pasuje --}}
<section class="siatka" style="padding-block:1.5rem 3.5rem">
  <div class="col-span-12">
    <p class="eva-kick" style="margin-bottom:1.5rem">{{ $fitKick }}</p>
    <div class="eva-quali">
      <div class="eva-quali__col">
        <p class="eva-quali__h eva-quali__h--yes">{{ $fitYes }}</p>
        <ul>@foreach ($yes as $y)<li>{{ $y }}</li>@endforeach</ul>
      </div>
      <div class="eva-quali__col eva-quali__col--no">
        <p class="eva-quali__h eva-quali__h--no">{{ $fitNo }}</p>
        <ul>@foreach ($no as $n)<li>{{ $n }}</li>@endforeach</ul>
      </div>
    </div>
  </div>
</section>

{{-- 6 — portal (istniejący partnerzy) --}}
<section class="eva-quote">
  <div class="siatka" style="align-items:center;row-gap:1.25rem">
    <div class="col-span-12 md:col-span-7">
      <p class="eva-kick" style="color:var(--eva-gold)">{{ $portalTitle }}</p>
      <p class="eva-quote__text" style="font-size:clamp(1.4rem,1rem+1.8vw,2.1rem);max-width:26ch">{{ $portalBody }}</p>
      @unless ($portal)
        <p style="margin:1.5rem 0 0;color:rgba(239,231,218,.75);max-width:46ch;line-height:1.6;font-size:.98rem">{{ $soon }}</p>
      @endunless
    </div>
    <div class="col-span-12 md:col-span-4 md:col-start-9">
      <div style="display:flex;flex-wrap:wrap;gap:.75rem">
        @if ($portal)
          <a href="{{ $portal }}" class="btn btn-kontra" rel="noopener" target="_blank">{{ $portalBtn }} ↗</a>
        @endif
        <a href="#partner-werden" class="btn {{ $portal ? 'btn-kontra-drugi' : 'btn-kontra' }}">{{ $formKick }}</a>
      </div>
    </div>
  </div>
</section>

{{-- 7 — Partner werden: formularz → CRM --}}
<section id="partner-werden" class="siatka" style="padding-block:4rem 4.5rem;align-items:start;row-gap:2rem">
  <div class="col-span-12 md:col-span-4">
    <p class="eva-kick">{{ $formKick }}</p>
    <h2 class="eva-row__title" style="margin-top:.75rem">{{ $formTitle }}</h2>
    <p class="eva-row__body">{{ $formBody }}</p>
  </div>
  <div class="col-span-12 md:col-span-7 md:col-start-6">
    @if (session('newsletter_status'))
      <p style="margin:0 0 1.25rem;color:var(--eva-tusz)">{{ session('newsletter_status') }}</p>
    @endif
    <form class="eva-form" method="post" action="{{ route('lead.store', ['locale' => $locale]) }}">
      @csrf
      <input type="hidden" name="wariant" value="a5">
      <input type="hidden" name="src" value="b2b-page">
      {{-- honeypot --}}
      <input type="text" name="firma_www" tabindex="-1" autocomplete="off" class="eva-hp" aria-hidden="true">

      <div class="eva-form__grid">
        <label class="eva-field eva-field--wide">
          <span class="eva-field__l">{{ $fShop }}</span>
          <input type="text" name="firma" required maxlength="200" autocomplete="organization">
        </label>
        <label class="eva-field">
          <span class="eva-field__l">{{ $fPerson }} <em>{{ $optional }}</em></span>
          <input type="text" name="person" maxlength="200" autocomplete="name">
        </label>
        <label class="eva-field">
          <span class="eva-field__l">{{ $fEmail }}</span>
          <input type="email" name="email" required maxlength="254" autocomplete="email">
        </label>
        <label class="eva-field">
          <span class="eva-field__l">{{ $fCity }} <em>{{ $optional }}</em></span>
          <input type="text" name="stadt" maxlength="120" autocomplete="address-level2">
        </label>
        <label class="eva-field">
          <span class="eva-field__l">{{ $fCountry }} <em>{{ $optional }}</em></span>
          <input type="text" name="land" maxlength="2" placeholder="DE" style="text-transform:uppercase">
        </label>
        <label class="eva-field eva-field--wide">
          <span class="eva-field__l">{{ $fWeb }} <em>{{ $optional }}</em></span>
          <input type="text" name="website" maxlength="200" inputmode="url" placeholder="https://">
        </label>
      </div>

      @error('firma')<p class="eva-form__err">{{ $message }}</p>@enderror
      @error('email')<p class="eva-form__err">{{ $message }}</p>@enderror

      <div class="eva-form__foot">
        <button type="submit" class="btn btn-glowny">{{ $fSubmit }}</button>
        <p class="eva-form__note">{{ $fConsent }}</p>
      </div>
    </form>
  </div>
</section>

@endsection
