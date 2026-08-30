<?php

declare(strict_types=1);

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Seo\Schemas\ProductSchema;
use Database\Seeders\DictionarySeeder;

beforeEach(function () {
    $this->seed(DictionarySeeder::class);
});

function makeProduct(): Product
{
    $product = Product::create([
        'model_no' => '901TP-AG',
        'category_id' => Category::where('slug', 'pierscionki')->value('id'),
        'spec' => ['material' => '925 Silber', 'model_no' => '901TP-AG', 'finish' => 'silber'],
        'sizes' => ['range' => '15–23', 'min' => 15, 'max' => 23],
        'status' => 'published',
    ]);

    $product->translations()->create([
        'locale' => 'de',
        'name' => 'Ringe 901TP-AG',
        'slug_localized' => '901-ringe-topas',
        'answer_summary' => 'Ringe 901TP-AG aus 925 Silber.',
        'faq' => [['q' => 'F?', 'a' => 'A.'], ['q' => 'F2?', 'a' => 'A2.']],
        'review_status' => 'approved',
    ]);

    return $product->fresh(['translations', 'category.translations']);
}

// GATE CI §9.1/§16: schema Product NIGDY nie zawiera offers ani price
test('product schema never contains offers', function () {
    $schema = app(ProductSchema::class)->build(makeProduct(), 'de', 'https://evastone.eu/de/x/');

    assertArrayNeverHasKey($schema, 'offers');
    assertArrayNeverHasKey($schema, 'price');
    assertArrayNeverHasKey($schema, 'priceCurrency');

    expect($schema['@type'])->toBe('Product')
        ->and($schema['sku'])->toBe('901TP-AG')
        ->and($schema['brand']['name'])->toBe('EvaStone');
});

test('schema json matches fixture contract', function () {
    $schema = app(ProductSchema::class)->build(makeProduct(), 'de', 'https://evastone.eu/de/x/');

    expect(array_keys($schema))->toBe([
        '@context', '@type', '@id', 'name', 'sku', 'mpn',
        'description', 'material', 'brand', 'category', 'additionalProperty',
    ]);
});
