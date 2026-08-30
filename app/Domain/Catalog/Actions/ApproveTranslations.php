<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Models\ProductTranslation;
use Illuminate\Support\Facades\Log;

/**
 * §7.3 — akceptacja hurtowa. Ta sama Action obsługuje bulk action
 * w Filament i komendę CLI. Zawsze z logiem kto i kiedy.
 */
class ApproveTranslations
{
    /**
     * @param  array<int>  $productIds
     * @param  array<string>|null  $locales  null = wszystkie
     */
    public function handle(array $productIds, ?array $locales, string $approvedBy): int
    {
        $query = ProductTranslation::whereIn('product_id', $productIds)
            ->whereIn('review_status', ['draft', 'client_review'])
            ->when($locales, fn ($q) => $q->whereIn('locale', $locales));

        $count = $query->count();

        $query->update([
            'review_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approvedBy,
        ]);

        Log::info('Akceptacja hurtowa tłumaczeń', [
            'products' => count($productIds),
            'translations' => $count,
            'locales' => $locales ?? 'all',
            'by' => $approvedBy,
        ]);

        return $count;
    }
}
