<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;

class CutTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['cut_id', 'locale', 'name', 'slug_localized'];
}
