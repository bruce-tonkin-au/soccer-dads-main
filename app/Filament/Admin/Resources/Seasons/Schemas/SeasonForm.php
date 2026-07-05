<?php

namespace App\Filament\Admin\Resources\Seasons\Schemas;

use App\Models\Night;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SeasonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('seasonName')
                    ->label('Season name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. Season 1, 2026'),
                Select::make('nightID')
                    ->label('Night')
                    ->options(fn (): array => Night::query()
                        ->where('nightActive', 1)
                        ->orderBy('nightSort')
                        ->pluck('nightName', 'nightID')
                        ->all())
                    ->required()
                    ->helperText('Which night this season belongs to (e.g. Friday, Tuesday).'),
                TextInput::make('seasonLink')
                    ->label('Season link (URL code)')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. 26S1')
                    ->unique(ignoreRecord: true),
                Select::make('seasonVisible')
                    ->label('Visible')
                    ->options([
                        1 => 'Visible',
                        0 => 'Hidden',
                    ])
                    ->default(1)
                    ->required()
                    ->selectablePlaceholder(false),
            ]);
    }
}
