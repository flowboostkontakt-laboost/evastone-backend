<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Leads\Models\Lead;
use App\Filament\Resources\LeadResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** §7.1 — LeadResource: read + zmiana statusu. Leady wchodzą z LP1. */
class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationLabel = 'Leady';

    protected static ?string $modelLabel = 'lead';

    protected static ?string $pluralModelLabel = 'leady';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('company_name')->disabled(),
            Forms\Components\TextInput::make('contact_name')->disabled(),
            Forms\Components\TextInput::make('email')->disabled(),
            Forms\Components\TextInput::make('phone')->disabled(),
            Forms\Components\TextInput::make('city')->disabled(),
            Forms\Components\TextInput::make('source')->disabled()->label('Źródło (?src=)'),
            Forms\Components\Textarea::make('message')->disabled()->columnSpanFull(),
            Forms\Components\Select::make('status')->options([
                'new' => 'nowy',
                'contacted' => 'skontaktowany',
                'qualified' => 'zakwalifikowany',
                'rejected' => 'odrzucony',
            ])->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('source')->badge()->label('Źródło'),
                Tables\Columns\TextColumn::make('locale')->badge(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'qualified' => 'success',
                    'rejected' => 'danger',
                    'contacted' => 'info',
                    default => 'warning',
                }),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
