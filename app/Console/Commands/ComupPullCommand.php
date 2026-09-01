<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Import\Actions\PullFromComup;
use App\Domain\Import\Actions\PullFromComupDump;
use App\Domain\Import\Actions\PullFromComupLive;
use App\Domain\Import\Models\ImportRun;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ComupPullCommand extends Command
{
    protected $signature = 'comup:pull
        {--tables=products : Zakres dumpu (products,categories,media)}
        {--source=mirror : Źródło: mirror (kopia statyczna), live (crawl evastone.eu) lub dump (pełny katalog B2B, schemat comup_legacy)}
        {--limit= : Ogranicz liczbę modeli (testowo; live i dump)}';

    protected $description = 'Dump ComUp (kopia starej strony / pełny katalog B2B) → import_products_raw';

    public function handle(PullFromComup $mirror, PullFromComupLive $live, PullFromComupDump $dump): int
    {
        $batchId = (string) Str::ulid();
        $run = ImportRun::begin($batchId, 'comup:pull --source='.$this->option('source'));

        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        $stats = match ($this->option('source')) {
            'live' => $live->handle($batchId, $limit),
            'dump' => $dump->handle($batchId, $limit),
            default => $mirror->handle($batchId),
        };

        $run->finish($stats);

        $this->info("Batch: {$batchId}");
        $this->table(array_keys($stats), [array_values($stats)]);

        return self::SUCCESS;
    }
}
