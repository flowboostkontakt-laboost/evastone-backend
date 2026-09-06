<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Http\Controllers\Controller;

/**
 * Schmuck = GALERIA (plan v1.0 §3): 20–30 przykładowych modeli, tylko zdjęcia
 * w jednym stylu, 6 kategorii jako filtry, lightbox. Bez numerów, opisów, cen
 * i „zapytaj". Opisy istnieją wyłącznie w portalu B2B.
 */
class CatalogController extends Controller
{
    private const LIMIT = 30;
    private const PER_CATEGORY = 5;

    /** 6 kategorii biżuterii (bez śmieciowych: pudełka, klipsy, unikaty…). */
    private const GALLERY_CATEGORIES = ['pierscionki', 'kolczyki', 'zawieszki', 'kolie', 'bransolety', 'sety'];

    public function index(string $locale, ?string $section = null, ?string $category = null)
    {
        abort_unless(in_array($locale, config('evastone.locales'), true), 404);
        $expectedSection = config("evastone.catalog_sections.{$locale}");
        if ($section !== null) {
            abort_unless($section === $expectedSection, 404);
        }

        $categoryModel = null;
        if ($category !== null) {
            $categoryModel = Category::whereHas('translations', fn ($q) => $q->where('locale', $locale)->where('slug_localized', $category))
                ->with('translations')
                ->firstOrFail();
        }

        if ($categoryModel) {
            // jedna kategoria: do 30 przykładów z tej kategorii
            $products = Product::with(['category.translations', 'media'])
                ->where('status', 'published')
                ->where('category_id', $categoryModel->id)
                ->whereHas('media')
                ->inRandomOrder()
                ->limit(self::LIMIT)
                ->get();
        } else {
            // selekcja zróżnicowana: po ~5 z każdej z 6 kategorii → ~30
            $catIds = Category::whereIn('slug', self::GALLERY_CATEGORIES)->pluck('id', 'slug');
            $products = collect();
            foreach (self::GALLERY_CATEGORIES as $slug) {
                if (! isset($catIds[$slug])) {
                    continue;
                }
                $products = $products->merge(
                    Product::with(['category.translations', 'media'])
                        ->where('status', 'published')
                        ->where('category_id', $catIds[$slug])
                        ->whereHas('media')
                        ->inRandomOrder()
                        ->limit(self::PER_CATEGORY)
                        ->get()
                );
            }
            $products = $products->take(self::LIMIT);
        }

        // kategorie do filtrów (tylko te 6, które mają opublikowane produkty)
        $filterCats = Category::whereIn('slug', self::GALLERY_CATEGORIES)
            ->whereHas('products', fn ($q) => $q->where('status', 'published'))
            ->with('translations')
            ->orderBy('position')
            ->get();

        return view('catalog.gallery', [
            'products' => $products,
            'locale' => $locale,
            'section' => $expectedSection,
            'category' => $categoryModel,
            'filterCats' => $filterCats,
        ]);
    }
}
