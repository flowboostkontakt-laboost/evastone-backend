<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Seo\Schemas\ProductSchema;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Jeden dyspozytor dla /{locale} i /{locale}/{path}:
 *  - pusto            → statyczna strona główna (hero z prototypu)
 *  - {section}/…       → katalog (wszystkie / kategoria / produkt) — dynamiczne
 *  - inne             → statyczna podstrona z prototypu (Atelier, Journal, Recht…)
 *
 * Strony statyczne trzymamy w resources/prototype (NIE w public/), żeby
 * katalog /{locale}/{section} trafiał do Laravela, a nie do serwera plików.
 */
class PageController extends Controller
{
    public function handle(Request $request, string $locale, ?string $path = null)
    {
        abort_unless(in_array($locale, config('evastone.locales'), true), 404);
        $section = config("evastone.catalog_sections.{$locale}");
        $path = trim((string) $path, '/');

        if ($path === '') {
            // nowa strona główna wg planu v1.0 §3 (nie stary hero z prototypu)
            return app(HomeController::class)->index($locale);
        }

        $seg = explode('/', $path);

        if ($seg[0] === $section) {
            $rest = array_slice($seg, 1);

            return match (count($rest)) {
                0 => app(CatalogController::class)->index($locale, $section, null),
                1 => app(CatalogController::class)->index($locale, $section, $rest[0]),
                // strony produktów usunięte (plan v1.0: galeria, opisy tylko w B2B)
                // — stare linki /{cat}/{model} → przekierowanie do galerii kategorii
                2 => redirect("/{$locale}/{$section}/{$rest[0]}/", 301),
                default => abort(404),
            };
        }

        return $this->serveStatic($locale, $path);
    }

    /** Statyczna strona prototypu z resources/prototype/{locale}/{path}/index.html. */
    private function serveStatic(string $locale, string $path): \Symfony\Component\HttpFoundation\Response
    {
        $rel = $path === '' ? "{$locale}/index.html" : "{$locale}/{$path}/index.html";
        $file = resource_path("prototype/{$rel}");
        abort_unless(is_file($file), 404);

        return response()->file($file);
    }
}
