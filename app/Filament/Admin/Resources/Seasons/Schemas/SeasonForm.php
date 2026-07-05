<?php

namespace App\Filament\Admin\Resources\Seasons\Schemas;

use App\Models\Member;
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
                Select::make('seasonListed')
                    ->label('List on public Seasons page')
                    ->options([
                        1 => 'Listed',
                        0 => 'Unlisted',
                    ])
                    ->default(1)
                    ->required()
                    ->selectablePlaceholder(false)
                    ->helperText('Controls the public Seasons list only — a season can be registerable (Visible) but not listed here.'),

                // Places are stored in season-awards (awardPlayer1/2/3), not on the
                // seasons row. Loaded/saved by EditSeason via season-awards.
                Select::make('award1')
                    ->label('1st place')
                    ->options(fn (): array => self::memberOptions())
                    ->searchable()
                    ->placeholder('None'),
                Select::make('award2')
                    ->label('2nd place')
                    ->options(fn (): array => self::memberOptions())
                    ->searchable()
                    ->placeholder('None'),
                Select::make('award3')
                    ->label('3rd place')
                    ->options(fn (): array => self::memberOptions())
                    ->searchable()
                    ->placeholder('None'),
            ]);
    }

    /** Active members, "Last First" order, keyed by memberID — for the place pickers. */
    protected static function memberOptions(): array
    {
        return Member::query()
            ->where('memberActive', 1)
            ->orderBy('memberNameLast')
            ->orderBy('memberNameFirst')
            ->get()
            ->mapWithKeys(fn (Member $m): array => [
                $m->memberID => trim($m->memberNameLast . ', ' . $m->memberNameFirst),
            ])
            ->all();
    }
}
