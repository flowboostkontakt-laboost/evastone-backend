<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'locale', 'name', 'slug_localized', 'answer_summary',
        'description', 'faq', 'meta_title', 'meta_description',
        'review_status', 'approved_at', 'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'faq' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Komplet AEO: answer_summary + min. 2 pary FAQ (§7.2, KPI §7.3). */
    public function isAeoComplete(): bool
    {
        return filled($this->answer_summary) && count($this->faq ?? []) >= 2;
    }
}
