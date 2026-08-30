<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'category_id', 'locale', 'name', 'slug_localized',
        'meta_title', 'meta_description', 'answer_summary',
    ];
}
