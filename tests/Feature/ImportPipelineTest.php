<?php

declare(strict_types=1);

use App\Domain\Catalog\Actions\ApproveTranslations;
use App\Domain\Catalog\Models\Product;
use App\Domain\Import\Actions\NormalizeBatch;
use App\Domain\Import\Actions\PublishBatch;
use App\Domain\Import\Actions\PullFromComup;
use App\Domain\Import\Actions\ValidateBatch;
use App\Domain\Import\Models\ImportProductRaw;
use Database\Seeders\DictionarySeeder;

beforeEach(function () {
    $this->seed(DictionarySeeder::class);
    config()->set('evastone.import.mirror_path', base_path('tests/Fixtures/mirror'));
});

function runPipeline(string $batch): array
{
    $stats = [];
    $stats['pull'] = app(PullFromComup::class)->handle($batch);
    $stats['normalize'] = app(NormalizeBatch::class)->handle($batch);
    $stats['validate'] = app(ValidateBatch::class)->handle($batch);
    $stats['publish'] = app(PublishBatch::class)->handle();

    return $stats;
}

test('full pipeline migrates a product from the comup mirror', function () {
    $stats = runPipeline('01TESTBATCH0000000000000001');

    expect($stats['pull']['inserted'])->toBe(1)
        ->and($stats['normalize']['normalized'])->toBe(1)
        ->and($stats['validate']['valid'])->toBe(1)
        ->and($stats['publish']['draft'])->toBe(1);

    $product = Product::with(['translations', 'category', 'stone', 'cut', 'collection'])->firstOrFail();

    expect($product->model_no)->toBe('901TP-AG')
        ->and($product->category->slug)->toBe('pierscionki')
        ->and($product->stone->slug)->toBe('topaz')
        ->and($product->cut->slug)->toBe('navette')
        ->and($product->collection->slug)->toBe('struktur')
        ->and($product->spec['material'])->toBe('925 Silber')
        ->and($product->spec['finish'])->toBe('silber')
        ->and($product->sizes['min'])->toBe(15)
        ->and($product->translations)->toHaveCount(3)
        ->and($product->status)->toBe('draft') // czeka na akceptację klientki
        ->and($product->getMedia('gallery'))->toHaveCount(1);

    $de = $product->translation('de');
    expect($de->review_status)->toBe('client_review')
        ->and($de->answer_summary)->toContain('925 Silber')
        ->and(count($de->faq))->toBeGreaterThanOrEqual(2);
});

// GATE: publikacja wymaga approved na WSZYSTKICH trzech locale (§3.2)
test('publication requires approval on all locales', function () {
    runPipeline('01TESTBATCH0000000000000002');
    $product = Product::firstOrFail();

    // częściowa akceptacja nie wystarcza
    app(ApproveTranslations::class)->handle([$product->id], ['de', 'en'], 'test');
    $product->translations()->where('locale', 'pl')->update(['review_status' => 'client_review']);
    app(PublishBatch::class)->handle();
    expect($product->fresh()->status)->not->toBe('published');

    // komplet akceptacji publikuje falę
    app(ApproveTranslations::class)->handle([$product->id], null, 'test');
    $product->fresh()->translations()->update(['review_status' => 'approved']);
    app(PublishBatch::class)->handle();

    $fresh = $product->fresh();
    expect($fresh->status)->toBe('published')
        ->and($fresh->published_at)->not->toBeNull();
});

// GATE §4.2: import puszczany wielokrotnie daje ten sam wynik
test('pipeline is idempotent across repeated runs', function () {
    runPipeline('01TESTBATCH0000000000000003');
    $second = runPipeline('01TESTBATCH0000000000000004');

    expect(ImportProductRaw::count())->toBe(1)
        ->and($second['pull']['inserted'])->toBe(0)
        ->and($second['pull']['unchanged'])->toBe(1)
        ->and(Product::count())->toBe(1)
        ->and(Product::first()->getMedia('gallery'))->toHaveCount(1);
});

// GATE §4.4: cena w treści blokuje publikację
test('price in description blocks publication', function () {
    $batch = '01TESTBATCH0000000000000005';
    app(PullFromComup::class)->handle($batch);
    app(NormalizeBatch::class)->handle($batch);

    $row = ImportProductRaw::firstOrFail();
    $normalized = $row->normalized;
    $normalized['translations']['pl']['answer_summary'] .= ' Cena od 1200 zł.';
    $row->update(['normalized' => $normalized]);

    app(ValidateBatch::class)->handle($batch);
    expect($row->fresh()->blockers())->toContain('cena w treści opisu: pl');

    app(PublishBatch::class)->handle();
    expect(Product::first()->status)->toBe('needs_review');
});
