<?php

declare(strict_types=1);

namespace App\Domain\Leads\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'source', 'locale', 'company_name', 'contact_name', 'email', 'phone',
        'city', 'country', 'message', 'utm', 'ip_hash', 'user_agent', 'status',
    ];

    protected function casts(): array
    {
        return ['utm' => 'array'];
    }
}
