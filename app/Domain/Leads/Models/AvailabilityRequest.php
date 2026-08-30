<?php

declare(strict_types=1);

namespace App\Domain\Leads\Models;

use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilityRequest extends Model
{
    protected $fillable = [
        'product_id', 'locale', 'name', 'email', 'postal_code', 'message',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
