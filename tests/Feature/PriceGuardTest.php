<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

// GATE CI §3.3/§16: zakaz absolutny pola price w modelu products
test('products table has no price column', function () {
    $columns = Schema::getColumnListing('products');

    expect($columns)->not->toContain('price')
        ->and($columns)->not->toContain('price_amount')
        ->and($columns)->not->toContain('currency');
});

// GATE CI §4.4/§16: regex cenowy łapie każdą walutę z treści
test('no price in product description', function (string $text) {
    expect(preg_match(config('evastone.import.price_regex'), $text))->toBe(1);
})->with([
    'Pierścionek za 1200 zł tylko teraz',
    'Cena: 350 PLN brutto',
    'ab 49 € erhältlich',
    'only 1,299 EUR',
]);

test('clean descriptions pass the price guard', function () {
    $clean = 'Ringe 901TP-AG aus 925 Silber. Der Stein ist Topas im Schliff Navette.';

    expect(preg_match(config('evastone.import.price_regex'), $clean))->toBe(0);
});
