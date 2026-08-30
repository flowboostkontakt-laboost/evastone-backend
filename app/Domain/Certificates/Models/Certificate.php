<?php

declare(strict_types=1);

namespace App\Domain\Certificates\Models;

use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Certificate extends Model
{
    protected $fillable = [
        'uid', 'product_id', 'serial_no', 'workshop', 'issued_at',
        'level', 'last_verified_at', 'revoked', 'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'last_verified_at' => 'datetime',
            'revoked' => 'boolean',
            'is_demo' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** §11 — uid NIGDY inkrementalny: ULID (nie da się wyliczyć numeracji). */
    public static function generateUid(): string
    {
        return strtolower((string) Str::ulid());
    }
}
