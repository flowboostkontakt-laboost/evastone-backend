<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Import\Actions\PublishBatch;
use App\Domain\Import\Models\ImportRun;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportPublishCommand extends Command
{
    protected $signature = 'import:publish
        {--category= : Publikacja falą per kategoria (slug techniczny, np. pierscionki)}
        {--batch= : Ogranicz do jednego przebiegu}
        {--dry-run : Tylko policz, nic nie zapisuj}';

    protected $description = 'Upsert produktów ze staging → katalog (fale per kategoria, §4.1 krok 7)';

    public function handle(PublishBatch $action): int
    {
        $run = ImportRun::begin($this->option('batch') ?? (string) Str::ulid(), 'import:publish');

        $stats = $action->handle(
            category: $this->option('category'),
            dryRun: (bool) $this->option('dry-run'),
            batchId: $this->option('batch'),
        );

        $run->finish($stats);

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN — nic nie zapisano.');
        }
        $this->table(array_keys($stats), [array_values($stats)]);

        return self::SUCCESS;
    }
}
