<!doctype html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EvaStone — Katalog ({{ strtoupper($locale) }})</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 60rem; margin: 2rem auto; padding: 0 1rem; color: #1a1712; background: #efe7da; }
        ul { list-style: none; padding: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(16rem, 1fr)); gap: .75rem; }
        li { background: #fff; padding: .75rem 1rem; border: 1px solid #d8cfbf; }
        a { color: inherit; }
    </style>
</head>
<body>
    <h1>EvaStone — Katalog</h1>
    <p>{{ $products->count() }} {{ ['de' => 'Modelle', 'en' => 'models', 'pl' => 'modeli'][$locale] }}</p>
    <ul>
        @foreach ($products as $product)
            @php($t = $product->translation($locale))
            @php($cat = $product->category->translation($locale))
            <li>
                <a href="/{{ $locale }}/{{ $section }}/{{ $cat->slug_localized }}/{{ $t->slug_localized }}/">
                    <strong>{{ $t->name }}</strong>
                </a>
                <br><small>{{ $cat->name }} · {{ $product->model_no }}</small>
            </li>
        @endforeach
    </ul>
</body>
</html>
