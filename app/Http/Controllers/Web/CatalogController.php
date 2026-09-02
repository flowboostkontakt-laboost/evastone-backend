<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Http\Controllers\Controller;

class CatalogController extends Controller
{
    /** /{locale}/{section}/ oraz /{locale}/{section}/{category}/ */
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

        $products = Product::with(['translations', 'category.translations', 'stone.translations', 'media'])
            ->where('status', 'published')
            ->when($categoryModel, fn ($q) => $q->where('category_id', $categoryModel->id))
            ->orderBy('model_no')
            ->get();

        return view('catalog.index', [
            'products' => $products,
            'locale' => $locale,
            'section' => $expectedSection,
            'category' => $categoryModel,
        ]);
    }
}
