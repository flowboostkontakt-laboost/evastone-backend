<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Catalog\Actions\ApproveTranslations;
use App\Domain\Catalog\Models\Product;
use Illuminate\Console\Command;

/**
 * CLI-odpowiednik akceptacji hurtowej z Filament (§7.3) — ta sama Action.
 */
class CatalogApproveCommand extends Command
{
    protected $signature = 'catalog:approve
        {--category= : Slug techniczny kategorii (np. pierscionki); brak = wszystkie}
        {--locale=* : Ogranicz do locale (de/en/pl); brak = wszystkie}
        {--by=cli : Kto zatwierdza (do logu)}';

    protected $description = 'Akceptacja hurtowa tłumaczeń produktów (client_review → approved)';

    public function handle(ApproveTranslations $action): int
    {
        $products = Product::query()
            ->when($this->option('category'), fn ($q, $slug) => $q->whereHas(
                'category', fn ($c) => $c->where('slug', $slug),
            ))
            ->pluck('id')
            ->all();

        $locales = $this->option('locale') ?: null;

        $count = $action->handle($products, $locales, (string) $this->option('by'));

        $this->info("Zatwierdzono {$count} tłumaczeń dla ".count($products).' produktów.');

        return self::SUCCESS;
    }
}
