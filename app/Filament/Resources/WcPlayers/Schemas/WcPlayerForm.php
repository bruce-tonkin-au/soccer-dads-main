<?php

namespace App\Filament\Resources\WcPlayers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WcPlayerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('teamID')
                    ->label('Team')
                    ->relationship('team', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('position')
                    ->maxLength(255),
                TextInput::make('shirt_number')
                    ->label('Shirt number')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(99),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
