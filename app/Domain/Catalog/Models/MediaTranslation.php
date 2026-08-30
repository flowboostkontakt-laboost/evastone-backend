<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;

/** §6.2 — alt-y opisowe per locale dla mediów. */
class MediaTranslation extends Model
{
    protected $fillable = ['media_id', 'locale', 'alt'];
}
