<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Catalog\Actions\ApproveTranslations;
use App\Domain\Catalog\Models\Product;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\TranslationsRelationManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Produkty';

    protected static ?string $modelLabel = 'produkt';

    protected static ?string $pluralModelLabel = 'produkty';

    /** Produkty wchodzą przez pipeline importu, nie ręcznie. */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        // §7.2 — spec to pola typowane, nie textarea. Treści per locale
        // (answer_summary → faq → description) są w relacji tłumaczeń,
        // z tą samą wymuszoną kolejnością.
        return $form->schema([
            Forms\Components\Section::make('Specyfikacja')->schema([
                Forms\Components\TextInput::make('model_no')->disabled()->label('Numer modelu (Comarch)'),
                Forms\Components\Select::make('category_id')->relationship('category', 'slug')->required()->label('Kategoria'),
                Forms\Components\Select::make('stone_id')->relationship('stone', 'slug')->label('Kamień (lista zamknięta)'),
                Forms\Components\Select::make('cut_id')->relationship('cut', 'slug')->label('Szlif (lista zamknięta)'),
                Forms\Components\Select::make('collection_id')->relationship('collection', 'slug')->label('Kolekcja'),
                Forms\Components\Select::make('spec.finish')->options(
                    array_combine(config('evastone.finishes'), config('evastone.finishes')),
                )->label('Wykończenie'),
                Forms\Components\TextInput::make('spec.material')->label('Materiał'),
            ])->columns(2),

            Forms\Components\Section::make('Status')->schema([
                Forms\Components\Select::make('status')->options([
                    'draft' => 'szkic',
                    'needs_review' => 'do wyjaśnienia',
                    'published' => 'opublikowany',
                    'archived' => 'zarchiwizowany',
                ])->required(),
                Forms\Components\Toggle::make('is_bestseller')->label('Bestseller'),
                Forms\Components\TextInput::make('bestseller_rank')->numeric()->label('Pozycja bestsellera'),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('model_no')->searchable()->sortable()->label('Model'),
                Tables\Columns\TextColumn::make('category.slug')->badge()->label('Kategoria'),
                Tables\Columns\TextColumn::make('stone.slug')->label('Kamień'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'published' => 'success',
                    'needs_review' => 'danger',
                    'archived' => 'gray',
                    default => 'warning',
                }),
                Tables\Columns\TextColumn::make('approved_locales')
                    ->state(fn (Product $record) => implode(' ', $record->approvedLocales()) ?: '—')
                    ->label('Zatwierdzone locale'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->relationship('category', 'slug'),
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'szkic', 'needs_review' => 'do wyjaśnienia',
                    'published' => 'opublikowany', 'archived' => 'zarchiwizowany',
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                // §7.3 — akceptacja hurtowa: jedna operacja zamiast 150 kliknięć
                Tables\Actions\BulkAction::make('approve_translations')
                    ->label('Zatwierdź komplet tłumaczeń')
                    ->icon('heroicon-o-check-badge')
                    ->requiresConfirmation()
                    ->modalHeading('Akceptacja hurtowa')
                    ->modalDescription('Ustawia review_status = approved dla wszystkich locale zaznaczonych produktów. Operacja jest logowana (kto i kiedy).')
                    ->action(function (Collection $records): void {
                        $count = app(ApproveTranslations::class)->handle(
                            $records->pluck('id')->all(),
                            null,
                            auth()->user()?->email ?? 'filament',
                        );

                        Notification::make()
                            ->title("Zatwierdzono {$count} tłumaczeń")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [TranslationsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
