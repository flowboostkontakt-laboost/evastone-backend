<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cut extends Model
{
    protected $fillable = ['slug', 'position'];

    public function translations(): HasMany
    {
        return $this->hasMany(CutTranslation::class);
    }

    public function translation(string $locale): ?CutTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }
}
