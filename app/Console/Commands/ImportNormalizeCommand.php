<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Import\Actions\NormalizeBatch;
use App\Domain\Import\Actions\NormalizeLiveBatch;
use App\Domain\Import\Models\ImportRun;
use Illuminate\Console\Command;

class ImportNormalizeCommand extends Command
{
    protected $signature = 'import:normalize {batch : ULID przebiegu comup:pull}';

    protected $description = 'Mapowanie surowych rekordów na słowniki (raw → normalized)';

    public function handle(NormalizeBatch $mirror, NormalizeLiveBatch $live): int
    {
        $batchId = (string) $this->argument('batch');
        $run = ImportRun::begin($batchId, 'import:normalize');

        $mirrorStats = $mirror->handle($batchId);
        $liveStats = $live->handle($batchId);
        $stats = [
            'normalized' => $mirrorStats['normalized'] + $liveStats['normalized'],
            'rejected' => $mirrorStats['rejected'] + $liveStats['rejected'],
        ];

        $run->finish($stats);
        $this->table(array_keys($stats), [array_values($stats)]);

        return self::SUCCESS;
    }
}
