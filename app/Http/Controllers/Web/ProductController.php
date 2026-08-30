<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductTranslation;
use App\Domain\Seo\Schemas\ProductSchema;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /** /{locale}/{section}/{category}/{slug} — §8.1 */
    public function show(Request $request, string $locale, string $section, string $category, string $slug, ProductSchema $schema)
    {
        abort_unless(in_array($locale, config('evastone.locales'), true), 404);
        abort_unless(config("evastone.catalog_sections.{$locale}") === $section, 404);

        $translation = ProductTranslation::where('locale', $locale)
            ->where('slug_localized', $slug)
            ->firstOrFail();

        $product = Product::with(['translations', 'category.translations', 'stone.translations', 'cut.translations', 'collection.translations'])
            ->whereKey($translation->product_id)
            ->where('status', 'published')
            ->firstOrFail();

        abort_unless($product->category->translation($locale)?->slug_localized === $category, 404);

        // §8.2 — hreflang tylko dla approved locale
        $alternates = [];
        foreach ($product->approvedLocales() as $altLocale) {
            $altT = $product->translation($altLocale);
            $altSection = config("evastone.catalog_sections.{$altLocale}");
            $altCategory = $product->category->translation($altLocale)?->slug_localized;
            if ($altT && $altCategory) {
                $alternates[$altLocale] = url("/{$altLocale}/{$altSection}/{$altCategory}/{$altT->slug_localized}");
            }
        }

        return view('catalog.product', [
            'product' => $product,
            't' => $product->translation($locale),
            'locale' => $locale,
            'alternates' => $alternates,
            'schemaJson' => json_encode(
                $schema->build($product, $locale, $request->url()),
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT,
            ),
        ]);
    }
}
