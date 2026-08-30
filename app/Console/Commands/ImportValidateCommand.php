<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Import\Actions\ValidateBatch;
use App\Domain\Import\Models\ImportRun;
use Illuminate\Console\Command;

class ImportValidateCommand extends Command
{
    protected $signature = 'import:validate {batch : ULID przebiegu}';

    protected $description = 'Reguły blokujące publikację (§4.4) na znormalizowanych rekordach';

    public function handle(ValidateBatch $action): int
    {
        $batchId = (string) $this->argument('batch');
        $run = ImportRun::begin($batchId, 'import:validate');

        $stats = $action->handle($batchId);

        $run->finish($stats);
        $this->table(array_keys($stats), [array_values($stats)]);

        return self::SUCCESS;
    }
}
