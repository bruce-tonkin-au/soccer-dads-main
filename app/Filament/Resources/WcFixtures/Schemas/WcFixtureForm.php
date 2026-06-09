<?php

namespace App\Filament\Resources\WcFixtures\Schemas;

use App\Models\WcFixture;
use App\Models\WcPlayer;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class WcFixtureForm
{
    public const STATUSES = [
        'scheduled' => 'Scheduled',
        'live' => 'Live',
        'completed' => 'Completed',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Match details')
                    ->columns(2)
                    ->schema([
                        Select::make('stage')
                            ->options([
                                'group' => 'Group',
                                'round_of_32' => 'Round of 32',
                                'round_of_16' => 'Round of 16',
                                'quarter_final' => 'Quarter-final',
                                'semi_final' => 'Semi-final',
                                'final' => 'Final',
                            ])
                            ->default('group')
                            ->required(),
                        Select::make('group_letter')
                            ->label('Group')
                            ->options(array_combine(range('A', 'L'), range('A', 'L'))),
                        Select::make('home_team_id')
                            ->label('Home team')
                            ->relationship('homeTeam', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('away_team_id')
                            ->label('Away team')
                            ->relationship('awayTeam', 'name')
                            ->searchable()
                            ->preload(),
                        DateTimePicker::make('match_datetime')
                            ->label('Kick-off (UTC)')
                            ->seconds(false),
                        TextInput::make('venue')
                            ->maxLength(150),
                        Select::make('status')
                            ->options(self::STATUSES)
                            ->default('scheduled')
                            ->required()
                            ->live(),
                    ]),

                Section::make('Result entry')
                    ->columns(2)
                    ->visible(fn (Get $get): bool => in_array($get('status'), ['completed', 'live'], true))
                    ->schema([
                        TextInput::make('home_score')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('away_score')
                            ->numeric()
                            ->minValue(0),
                    ]),

                Section::make('Goalscorers')
                    ->description('Tick each player who scored. Saving syncs the goal records for this fixture.')
                    // Hidden on create — there are no teams/players to pick from until the fixture exists.
                    ->visible(fn (?WcFixture $record): bool => $record !== null)
                    ->schema([
                        CheckboxList::make('goalscorers')
                            ->label('Goals')
                            ->options(fn (?WcFixture $record): array => self::playerOptions($record))
                            ->descriptions(fn (?WcFixture $record): array => self::playerTeamDescriptions($record))
                            ->columns(2)
                            ->bulkToggleable()
                            ->dehydrated(false),
                        Select::make('own_goals')
                            ->label('Own goals (edge cases)')
                            ->helperText('Recorded as own goals; these never award player points.')
                            ->multiple()
                            ->options(fn (?WcFixture $record): array => self::playerOptions($record))
                            ->searchable()
                            ->dehydrated(false),
                    ]),
            ]);
    }

    /**
     * Active players of both teams in this fixture, keyed by playerID.
     * Label: "Flag Team — #shirt Name".
     */
    public static function playerOptions(?WcFixture $record): array
    {
        if (! $record) {
            return [];
        }

        $teamIds = array_filter([$record->home_team_id, $record->away_team_id]);

        if (empty($teamIds)) {
            return [];
        }

        return WcPlayer::query()
            ->with('team')
            ->whereIn('teamID', $teamIds)
            ->where('is_active', true)
            ->orderBy('teamID')
            ->orderBy('shirt_number')
            ->get()
            ->mapWithKeys(fn (WcPlayer $p) => [
                $p->playerID => trim(
                    ($p->team?->flag ? $p->team->flag . ' ' : '')
                    . ($p->team?->name ? $p->team->name . ' — ' : '')
                    . ($p->shirt_number ? '#' . $p->shirt_number . ' ' : '')
                    . $p->name
                ),
            ])
            ->all();
    }

    /**
     * Per-player team descriptions, keyed by playerID (used under each checkbox).
     */
    public static function playerTeamDescriptions(?WcFixture $record): array
    {
        if (! $record) {
            return [];
        }

        $teamIds = array_filter([$record->home_team_id, $record->away_team_id]);

        if (empty($teamIds)) {
            return [];
        }

        return WcPlayer::query()
            ->with('team')
            ->whereIn('teamID', $teamIds)
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(fn (WcPlayer $p) => [
                $p->playerID => trim(($p->team?->flag ? $p->team->flag . ' ' : '') . ($p->team?->name ?? '')),
            ])
            ->all();
    }
}
