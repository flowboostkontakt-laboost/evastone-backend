<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Import\Models\ImportProductRaw;
use App\Filament\Resources\ImportReviewResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** §4.1 krok 6 + §7.1 — przegląd staging importu (kolejka ręczna). */
class ImportReviewResource extends Resource
{
    protected static ?string $model = ImportProductRaw::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationLabel = 'Import — przegląd';

    protected static ?string $modelLabel = 'rekord importu';

    protected static ?string $pluralModelLabel = 'rekordy importu';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('external_id')->searchable()->label('Model'),
                Tables\Columns\TextColumn::make('batch_id')->limit(12)->label('Batch'),
                Tables\Columns\TextColumn::make('state')->badge()->color(fn (string $state) => match ($state) {
                    'imported' => 'success',
                    'normalized' => 'info',
                    'rejected' => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('blockers')
                    ->state(fn (ImportProductRaw $record) => implode('; ', $record->blockers()) ?: '—')
                    ->wrap()
                    ->label('Blokery'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Utworzony'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('state')->options([
                    'raw' => 'raw', 'normalized' => 'normalized',
                    'rejected' => 'rejected', 'imported' => 'imported',
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListImportReviews::route('/')];
    }
}
