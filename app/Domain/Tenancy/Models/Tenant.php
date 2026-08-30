<?php

declare(strict_types=1);

namespace App\Domain\Tenancy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'slug', 'domain', 'is_demo', 'catalog_mode', 'featured_count', 'brand',
    ];

    protected function casts(): array
    {
        return [
            'is_demo' => 'boolean',
            'catalog_mode' => 'boolean',
            'brand' => 'array',
        ];
    }

    public function demoPrices(): HasMany
    {
        return $this->hasMany(DemoPrice::class);
    }
}
