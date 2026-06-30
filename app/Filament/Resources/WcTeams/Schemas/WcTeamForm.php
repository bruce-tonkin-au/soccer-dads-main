<?php

namespace App\Filament\Resources\WcTeams\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WcTeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('qualified')
                    ->label('In Tournament')
                    ->helperText('Switch off to mark this team as eliminated — they will appear struck through on the ladder. Auto-derived from knockout fixtures once those are loaded, so manual overrides become unnecessary as the draw fills in.')
                    ->inline(false)
                    ->default(true),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->label('Code')
                    ->required()
                    ->maxLength(10),
                TextInput::make('flag')
                    ->label('Flag emoji')
                    ->maxLength(20),
                TextInput::make('confederation')
                    ->required()
                    ->maxLength(255),
                TextInput::make('fifa_ranking')
                    ->label('FIFA ranking')
                    ->numeric()
                    ->minValue(1),
                Select::make('pot')
                    ->options([1 => 'Pot 1', 2 => 'Pot 2', 3 => 'Pot 3', 4 => 'Pot 4'])
                    ->required(),
                Select::make('seed_tier')
                    ->label('Seed tier')
                    ->options([1 => 'Top 24', 2 => 'Bottom 24'])
                    ->required(),
                Select::make('group_letter')
                    ->label('Group')
                    ->options(array_combine(range('A', 'L'), range('A', 'L'))),
                Select::make('group_position')
                    ->label('Group position')
                    ->options([1 => 1, 2 => 2, 3 => 3, 4 => 4]),
            ]);
    }
}
