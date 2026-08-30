<?php

declare(strict_types=1);

namespace App\Filament\Resources\ImportReviewResource\Pages;

use App\Filament\Resources\ImportReviewResource;
use Filament\Resources\Pages\ListRecords;

class ListImportReviews extends ListRecords
{
    protected static string $resource = ImportReviewResource::class;
}
