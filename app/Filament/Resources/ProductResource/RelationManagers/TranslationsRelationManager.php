<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TranslationsRelationManager extends RelationManager
{
    protected static string $relationship = 'translations';

    protected static ?string $title = 'Treści (de / en / pl)';

    /**
     * §7.2 — kolejność pól jest KONTRAKTEM, nie preferencją:
     * 1. answer_summary (40–60 słów, blokada zapisu poza zakresem)
     * 2. spec — pola typowane na poziomie produktu
     * 3. faq — repeater 2–4 pary
     * 4. description — dopiero na końcu
     */
    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('locale')->disabled(),
            Forms\Components\TextInput::make('name')->required()->label('Nazwa'),

            Forms\Components\Textarea::make('answer_summary')
                ->required()
                ->rows(4)
                ->live(debounce: 500)
                ->helperText(function (?string $state): string {
                    $words = count(preg_split('/\s+/u', trim((string) $state), -1, PREG_SPLIT_NO_EMPTY));

                    return "Licznik: {$words} słów (wymagane 40–60).";
                })
                ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail): void {
                    $words = count(preg_split('/\s+/u', trim((string) $value), -1, PREG_SPLIT_NO_EMPTY));
                    if ($words < 40 || $words > 60) {
                        $fail("Answer summary ma {$words} słów — kontrakt AEO wymaga 40–60.");
                    }
                })
                ->label('1. Answer summary (AEO)'),

            Forms\Components\Repeater::make('faq')
                ->schema([
                    Forms\Components\TextInput::make('q')->required()->label('Pytanie'),
                    Forms\Components\Textarea::make('a')->required()->rows(2)->label('Odpowiedź'),
                ])
                ->minItems(2)
                ->maxItems(4)
                ->label('3. FAQ (2–4 pary)'),

            Forms\Components\Textarea::make('description')
                ->rows(6)
                ->label('4. Rozwinięcie narracyjne (opcjonalne)'),

            Forms\Components\TextInput::make('meta_title')->label('Meta title'),
            Forms\Components\Textarea::make('meta_description')->rows(2)->label('Meta description'),
        ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('locale')
            ->columns([
                Tables\Columns\TextColumn::make('locale')->badge(),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\IconColumn::make('aeo_complete')
                    ->state(fn ($record) => $record->isAeoComplete())
                    ->boolean()
                    ->label('Komplet AEO'),
                Tables\Columns\TextColumn::make('review_status')->badge()->color(fn (string $state) => match ($state) {
                    'approved' => 'success',
                    'client_review' => 'warning',
                    default => 'gray',
                })->label('Akceptacja'),
                Tables\Columns\TextColumn::make('approved_by')->label('Zatwierdził(a)'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->headerActions([]);
    }
}
