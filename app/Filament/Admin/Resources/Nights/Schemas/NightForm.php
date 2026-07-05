<?php

namespace App\Filament\Admin\Resources\Nights\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NightForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nightName')
                    ->label('Night name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. Friday'),
                TextInput::make('nightVenue')
                    ->label('Venue')
                    ->maxLength(255)
                    ->placeholder('e.g. Windsor Gardens'),
                TextInput::make('nightAddress')
                    ->label('Address')
                    ->maxLength(255),
                Select::make('nightDayOfWeek')
                    ->label('Day of week')
                    ->options([
                        1 => 'Monday',
                        2 => 'Tuesday',
                        3 => 'Wednesday',
                        4 => 'Thursday',
                        5 => 'Friday',
                        6 => 'Saturday',
                        7 => 'Sunday',
                    ]),
                Select::make('nightActive')
                    ->label('Active')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->default(1)
                    ->required()
                    ->selectablePlaceholder(false),
                TextInput::make('nightSort')
                    ->label('Sort order')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
