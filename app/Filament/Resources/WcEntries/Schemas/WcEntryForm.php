<?php

namespace App\Filament\Resources\WcEntries\Schemas;

use App\Filament\Resources\WcEntries\WcEntryResource;
use App\Support\MemberDirectory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WcEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Entry')
                    ->columns(2)
                    ->schema([
                        TextInput::make('entry_name')
                            ->label('Entry name')
                            ->required()
                            ->maxLength(100),
                        Select::make('memberID')
                            ->label('Member')
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search): array => MemberDirectory::search($search))
                            ->getOptionLabelUsing(fn ($value): ?string => MemberDirectory::label((int) $value))
                            ->required(),
                        TextInput::make('orderID')
                            ->label('Order ID')
                            ->numeric()
                            ->helperText('Optional — links to a store order.'),
                        Toggle::make('draw_completed')
                            ->label('Draw completed')
                            ->helperText('Set automatically when both teams and all 4 players are assigned.')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                Section::make('Draw selections')
                    ->description('Set once the teams and players have been pulled from the hat.')
                    ->columns(2)
                    ->schema([
                        Select::make('tier1_team')
                            ->label('Top-24 team')
                            ->options(fn (): array => WcEntryResource::teamOptions(1))
                            ->searchable()
                            ->dehydrated(false),
                        Select::make('tier2_team')
                            ->label('Bottom-24 team')
                            ->options(fn (): array => WcEntryResource::teamOptions(2))
                            ->searchable()
                            ->dehydrated(false),
                        Select::make('player_slot1')
                            ->label('Player 1')
                            ->options(fn (): array => WcEntryResource::playerOptions())
                            ->searchable()
                            ->dehydrated(false),
                        Select::make('player_slot2')
                            ->label('Player 2')
                            ->options(fn (): array => WcEntryResource::playerOptions())
                            ->searchable()
                            ->dehydrated(false),
                        Select::make('player_slot3')
                            ->label('Player 3')
                            ->options(fn (): array => WcEntryResource::playerOptions())
                            ->searchable()
                            ->dehydrated(false),
                        Select::make('player_slot4')
                            ->label('Player 4')
                            ->options(fn (): array => WcEntryResource::playerOptions())
                            ->searchable()
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
