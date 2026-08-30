<?php

declare(strict_types=1);

namespace App\Domain\Tenancy\Models;

use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** §12.3 — jedyne miejsce w systemie, gdzie istnieje cena. */
class DemoPrice extends Model
{
    protected $fillable = ['tenant_id', 'product_id', 'amount', 'currency'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
