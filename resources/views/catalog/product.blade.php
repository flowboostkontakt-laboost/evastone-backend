<!doctype html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $t->meta_title ?? $t->name }}</title>
    <meta name="description" content="{{ $t->meta_description }}">
    @foreach ($alternates as $altLocale => $url)
        <link rel="alternate" hreflang="{{ $altLocale }}" href="{{ $url }}">
    @endforeach
    @if (isset($alternates[config('evastone.x_default')]))
        <link rel="alternate" hreflang="x-default" href="{{ $alternates[config('evastone.x_default')] }}">
    @endif
    <script type="application/ld+json">{!! $schemaJson !!}</script>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 60rem; margin: 2rem auto; padding: 0 1rem; color: #1a1712; background: #efe7da; }
        img { max-width: 24rem; width: 100%; height: auto; }
        dl { display: grid; grid-template-columns: max-content 1fr; gap: .25rem 1.5rem; }
        dt { font-weight: 600; }
    </style>
</head>
<body>
    <nav><a href="/{{ $locale }}/">EvaStone</a></nav>

    <h1>{{ $t->name }}</h1>

    @php($media = $product->getFirstMedia('gallery'))
    @if ($media)
        <img src="{{ $media->getUrl() }}"
             alt="{{ \App\Domain\Catalog\Models\MediaTranslation::where('media_id', $media->id)->where('locale', $locale)->value('alt') }}">
    @endif

    <p>{{ $t->answer_summary }}</p>

    <h2>Spezifikation</h2>
    <dl>
        @foreach ($product->spec as $key => $value)
            <dt>{{ $key }}</dt>
            <dd>{{ is_array($value) ? implode(', ', $value) : $value }}</dd>
        @endforeach
    </dl>

    @if ($t->faq)
        <h2>FAQ</h2>
        @foreach ($t->faq as $faq)
            <h3>{{ $faq['q'] }}</h3>
            <p>{{ $faq['a'] }}</p>
        @endforeach
    @endif
</body>
</html>
