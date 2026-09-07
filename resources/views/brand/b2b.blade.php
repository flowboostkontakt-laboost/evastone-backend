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

{{-- atuty --}}
<section class="siatka" style="padding-bottom:1rem">
  <div class="col-span-12 md:col-span-9">
    <ul class="eva-points">
      @foreach ($points as $p)<li>{{ $p }}</li>@endforeach
    </ul>
  </div>
</section>

{{-- portal / wejście --}}
<section class="eva-quote" style="margin-top:3.5rem">
  <div class="siatka" style="align-items:center;row-gap:1.5rem">
    <div class="col-span-12 md:col-span-7">
      <p class="eva-kick" style="color:var(--eva-gold)">{{ $portalTitle }}</p>
      <p class="eva-quote__text" style="font-size:clamp(1.4rem,1rem+1.8vw,2.1rem);max-width:28ch">{{ $portalBody }}</p>
      @unless ($portal)
        <p style="margin:1.5rem 0 0;color:rgba(239,231,218,.75);max-width:44ch;line-height:1.6;font-size:.98rem">{{ $soon }}</p>
      @endunless
    </div>
    <div class="col-span-12 md:col-span-4 md:col-start-9">
      <div style="display:flex;flex-wrap:wrap;gap:.75rem">
        @if ($portal)
          <a href="{{ $portal }}" class="btn btn-kontra" rel="noopener" target="_blank">{{ $portalBtn }} ↗</a>
        @endif
        <a href="{{ $lContact }}" class="btn {{ $portal ? 'btn-kontra-drugi' : 'btn-kontra' }}">{{ $contactBtn }}</a>
      </div>
    </div>
  </div>
</section>

@endsection
