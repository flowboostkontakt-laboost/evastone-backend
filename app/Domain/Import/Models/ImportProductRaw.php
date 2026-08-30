<?php

declare(strict_types=1);

namespace App\Domain\Import\Models;

use Illuminate\Database\Eloquent\Model;

class ImportProductRaw extends Model
{
    protected $table = 'import_products_raw';

    protected $fillable = [
        'batch_id', 'source', 'external_id', 'payload', 'normalized',
        'row_hash', 'state', 'errors',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'normalized' => 'array',
            'errors' => 'array',
        ];
    }

    public function blockers(): array
    {
        return $this->errors['blockers'] ?? [];
    }
}
