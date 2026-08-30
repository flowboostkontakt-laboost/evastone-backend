<?php

declare(strict_types=1);

namespace App\Domain\Consents\Models;

use Illuminate\Database\Eloquent\Model;

class Consent extends Model
{
    protected $fillable = [
        'subject_type', 'subject_id', 'type', 'granted',
        'granted_at', 'revoked_at', 'document_version', 'evidence',
    ];

    protected function casts(): array
    {
        return [
            'granted' => 'boolean',
            'granted_at' => 'datetime',
            'revoked_at' => 'datetime',
            'evidence' => 'array',
        ];
    }
}
