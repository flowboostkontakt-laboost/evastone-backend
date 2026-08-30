<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Import\Actions\GenerateDiscrepancyReport;
use Illuminate\Console\Command;

class ImportReportCommand extends Command
{
    protected $signature = 'import:report {batch : ULID przebiegu} {--format=csv}';

    protected $description = 'Raport rozbieżności dla klientki (§4.5)';

    public function handle(GenerateDiscrepancyReport $action): int
    {
        $path = $action->handle((string) $this->argument('batch'));

        $this->info("Raport zapisany: {$path}");

        return self::SUCCESS;
    }
}
