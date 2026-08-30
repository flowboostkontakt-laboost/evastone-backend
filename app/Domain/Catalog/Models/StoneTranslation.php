<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;

class StoneTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'stone_id', 'locale', 'name', 'slug_localized',
        'meta_title', 'meta_description', 'answer_summary', 'faq',
    ];

    protected function casts(): array
    {
        return ['faq' => 'array'];
    }
}
