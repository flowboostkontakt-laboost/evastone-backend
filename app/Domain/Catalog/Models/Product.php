<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * KONTRAKT §3.2/§3.3: model NIE MA i nigdy nie będzie miał pola `price`.
 * Ceny istnieją wyłącznie w tenancie demo (demo_prices). Test asercyjny w CI.
 */
class Product extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'model_no', 'category_id', 'stone_id', 'cut_id', 'collection_id',
        'spec', 'sizes', 'care_ref', 'is_bestseller', 'bestseller_rank',
        'status', 'published_at', 'source_hash',
    ];

    protected function casts(): array
    {
        return [
            'spec' => 'array',
            'sizes' => 'array',
            'is_bestseller' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stone(): BelongsTo
    {
        return $this->belongsTo(Stone::class);
    }

    public function cut(): BelongsTo
    {
        return $this->belongsTo(Cut::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function translation(string $locale): ?ProductTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    /** §8.2 — hreflang wyłącznie dla locale z review_status = approved. */
    public function approvedLocales(): array
    {
        return $this->translations
            ->where('review_status', 'approved')
            ->pluck('locale')
            ->values()
            ->all();
    }

    public function isPublishable(): bool
    {
        $approved = $this->approvedLocales();

        return count(array_intersect(config('evastone.locales'), $approved))
            === count(config('evastone.locales'));
    }

    /** §6.1 — kolekcje medialibrary. Konwersje zawsze w kolejce. */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('macro');
        $this->addMediaCollection('video');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        foreach (['thumb' => 400, 'card' => 800, 'full' => 1600] as $name => $width) {
            $this->addMediaConversion($name)
                ->width($width)
                ->format('webp')
                ->performOnCollections('gallery', 'macro')
                ->queued();
        }
    }
}
