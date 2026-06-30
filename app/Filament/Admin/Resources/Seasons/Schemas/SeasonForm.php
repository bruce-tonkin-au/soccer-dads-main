<?php

namespace App\Filament\Admin\Resources\Seasons\Schemas;

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
