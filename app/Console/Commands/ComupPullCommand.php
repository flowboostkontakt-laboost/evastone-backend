<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Import\Actions\PullFromComup;
use App\Domain\Import\Actions\PullFromComupLive;
use App\Domain\Import\Models\ImportRun;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ComupPullCommand extends Command
{
    protected $signature = 'comup:pull
        {--tables=products : Zakres dumpu (products,categories,media)}
        {--source=mirror : Źródło: mirror (kopia statyczna) lub live (crawl evastone.eu)}
        {--limit= : Ogranicz liczbę modeli (testowo, tylko live)}';

    protected $description = 'Dump ComUp (kopia starej strony) → import_products_raw';

    public function handle(PullFromComup $mirror, PullFromComupLive $live): int
    {
        $batchId = (string) Str::ulid();
        $run = ImportRun::begin($batchId, 'comup:pull --source='.$this->option('source'));

        $stats = $this->option('source') === 'live'
            ? $live->handle($batchId, $this->option('limit') !== null ? (int) $this->option('limit') : null)
            : $mirror->handle($batchId);

        $run->finish($stats);

        $this->info("Batch: {$batchId}");
        $this->table(array_keys($stats), [array_values($stats)]);

        return self::SUCCESS;
    }
}
