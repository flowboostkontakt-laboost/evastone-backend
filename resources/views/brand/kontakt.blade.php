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

{{-- dwa tory --}}
<section class="siatka">
  <div class="col-span-12">
    <div class="eva-forks">
      <div class="eva-fork">
        <h2 class="eva-fork__h">{{ $boutiqueTitle }}</h2>
        <p class="eva-fork__b">{{ $boutiqueBody }}</p>
        <div><a href="{{ $lB2b }}" class="btn btn-glowny">{{ $boutiqueBtn }}</a></div>
      </div>
      <div class="eva-fork">
        <h2 class="eva-fork__h">{{ $wearTitle }}</h2>
        <p class="eva-fork__b">{{ $wearBody }}</p>
        <div><a href="{{ $lStore }}" class="btn btn-drugi">{{ $wearBtn }}</a></div>
      </div>
    </div>
  </div>
</section>

{{-- dane bezpośrednie --}}
<section class="eva-sheet" style="margin-top:3.5rem">
  <div class="siatka">
    <div class="col-span-12 eva-sheet-in">
      <p class="eva-kick" style="margin-bottom:2rem">{{ $directTitle }}</p>
      <dl class="eva-contact">
        <div>
          <dt>{{ $mailLabel }}</dt>
          <dd><a href="mailto:{{ $addr['email'] }}">{{ $addr['email'] }}</a></dd>
        </div>
        <div>
          <dt>{{ $addrLabel }}</dt>
          <dd>{{ $addr['street'] }}<br>{{ $addr['city'] }}<br>{{ $addr['country'] }}</dd>
        </div>
        <div>
          <dt>{{ $phoneLabel }}</dt>
          <dd><a href="tel:{{ $addr['phone_href'] }}">{{ $addr['phone'] }}</a></dd>
        </div>
      </dl>
      <div style="border-top:1px solid var(--eva-linia);margin-top:1rem;padding-top:2rem;max-width:44rem">
        <p class="eva-card__kick">{{ $pressTitle }}</p>
        <p class="eva-row__body" style="margin-top:.6rem">{{ $pressBody }}</p>
      </div>
    </div>
  </div>
</section>

@endsection
