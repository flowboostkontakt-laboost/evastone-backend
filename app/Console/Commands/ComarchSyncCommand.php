<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Catalog\Actions\SyncComarchDictionary;
use Illuminate\Console\Command;

class ComarchSyncCommand extends Command
{
    protected $signature = 'comarch:sync';

    protected $description = 'Read-only sync słownika z Comarch (§5) — model_no, rozmiarówka';

    public function handle(SyncComarchDictionary $action): int
    {
        $result = $action->handle();

        $this->info(json_encode($result));

        return self::SUCCESS;
    }
}
