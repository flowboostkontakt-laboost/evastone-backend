<?php

declare(strict_types=1);

namespace App\Domain\Seo\Schemas;

use App\Domain\Catalog\Models\Product;

/**
 * §9.1 — schema.org Product: BEZ `offers`, BEZ `price`. Twarda reguła
 * modelu biznesowego (B2B przez butiki, zero cen publicznie).
 * Test asercyjny w CI: test_product_schema_never_contains_offers.
 */
class ProductSchema
{
    private const PROPERTY_LABELS = [
        'de' => ['stone' => 'Edelstein', 'cut' => 'Schliff', 'collection' => 'Kollektion', 'sizes' => 'Größenrange'],
        'en' => ['stone' => 'Gemstone', 'cut' => 'Cut', 'collection' => 'Collection', 'sizes' => 'Size range'],
        'pl' => ['stone' => 'Kamień', 'cut' => 'Szlif', 'collection' => 'Kolekcja', 'sizes' => 'Zakres rozmiarów'],
    ];

    public function build(Product $product, string $locale, string $url): array
    {
        $t = $product->translation($locale);
        $labels = self::PROPERTY_LABELS[$locale] ?? self::PROPERTY_LABELS['de'];

        $properties = [];
        foreach (['stone', 'cut', 'collection'] as $key) {
            $relation = $product->{$key};
            $name = $relation?->translation($locale)?->name
                ?? $relation?->translations?->firstWhere('locale', $locale)?->name;
            if ($name !== null) {
                $properties[] = [
                    '@type' => 'PropertyValue',
                    'name' => $labels[$key],
                    'value' => $name,
                ];
            }
        }
        if ($range = $product->sizes['range'] ?? null) {
            $properties[] = ['@type' => 'PropertyValue', 'name' => $labels['sizes'], 'value' => $range];
        }

        $image = $product->getFirstMediaUrl('gallery');

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            '@id' => "{$url}#product",
            'name' => $t?->name,
            'sku' => $product->model_no,
            'mpn' => $product->model_no,
            'description' => $t?->answer_summary,
            'material' => $product->spec['material'] ?? null,
            'image' => $image !== '' ? $image : null,
            'brand' => ['@type' => 'Brand', 'name' => 'EvaStone'],
            'category' => $product->category?->translation($locale)?->name,
            'additionalProperty' => $properties,
        ], fn ($v) => $v !== null && $v !== []);
    }
}
