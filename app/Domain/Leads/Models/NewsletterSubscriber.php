<?php

declare(strict_types=1);

namespace App\Domain\Leads\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'email', 'locale', 'token', 'confirmed_at', 'unsubscribed_at',
        'consent_text_version', 'ip_hash',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }
}
