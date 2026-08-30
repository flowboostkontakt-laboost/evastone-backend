<?php

declare(strict_types=1);

namespace App\Domain\Import\Models;

use Illuminate\Database\Eloquent\Model;

class ImportRun extends Model
{
    protected $fillable = [
        'batch_id', 'command', 'stats', 'status', 'started_at', 'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'stats' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public static function begin(string $batchId, string $command): self
    {
        return self::create([
            'batch_id' => $batchId,
            'command' => $command,
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function finish(array $stats): void
    {
        $this->update(['stats' => $stats, 'status' => 'finished', 'finished_at' => now()]);
    }
}
