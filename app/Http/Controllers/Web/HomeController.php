<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Strona główna wg planu v1.0 §3 „Start": ≤ 4 ekrany, ≤ 60 słów.
 * Ekran 1 hero (zdjęcie + jedno zdanie + 2 przyciski), ekran 2 kafle 6 kategorii,
 * ekran 3 podgląd mapy sklepów, ekran 4 Messen + newsletter (stopka).
 * Bez H1 „Oberflächenstruktur", slidera, sekcji problem→dowód (§3.2 WYPADA).
 */
class HomeController extends Controller
{
    private const CATEGORIES = ['pierscionki', 'kolczyki', 'zawieszki', 'kolie', 'bransolety', 'sety'];

    public function index(string $locale): View
    {
        abort_unless(in_array($locale, config('evastone.locales'), true), 404);

        $cats = Category::whereIn('slug', self::CATEGORIES)->with('translations')->orderBy('position')->get()
            ->sortBy(fn ($c) => array_search($c->slug, self::CATEGORIES, true));

        $tiles = [];
        foreach ($cats as $c) {
            $slug = $c->translation($locale)?->slug_localized;
            if (! $slug) {
                continue;
            }
            $product = Product::where('status', 'published')->where('category_id', $c->id)->whereHas('media')->inRandomOrder()->first();
            $tiles[] = [
                'name' => $c->translation($locale)?->name,
                'slug' => $slug,
                'image' => $product?->getFirstMedia('gallery')?->getUrl(),
            ];
        }

        return view('home', [
            'locale' => $locale,
            'tiles' => $tiles,
        ]);
    }
}
