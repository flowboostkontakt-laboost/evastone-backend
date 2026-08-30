<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Catalog\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * §7.3 — KPI z dok. 17 §5 na dashboardzie, nie w raporcie:
 * % produktów z kompletem answer_summary + spec + faq.
 */
class CatalogCompletenessWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $locales = config('evastone.locales');
        $products = Product::with('translations')->get();
        $total = $products->count();

        $aeoComplete = $products->filter(
            fn (Product $p) => collect($locales)->every(
                fn (string $l) => $p->translation($l)?->isAeoComplete() ?? false,
            ) && filled($p->spec),
        )->count();

        $approved = $products->filter(fn (Product $p) => $p->isPublishable())->count();
        $published = $products->where('status', 'published')->count();

        $pct = fn (int $n) => $total > 0 ? round($n / $total * 100).'%' : '—';

        return [
            Stat::make('Produkty w katalogu', (string) $total),
            Stat::make('Komplet AEO (summary+spec+faq)', $pct($aeoComplete))
                ->description("{$aeoComplete} z {$total}")
                ->color($aeoComplete === $total ? 'success' : 'warning'),
            Stat::make('Zatwierdzone 3× locale', $pct($approved))
                ->description("{$approved} z {$total}"),
            Stat::make('Opublikowane', $pct($published))
                ->description("{$published} z {$total}")
                ->color('success'),
        ];
    }
}
