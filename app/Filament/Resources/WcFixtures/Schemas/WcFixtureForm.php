<?php

namespace App\Filament\Resources\WcFixtures\Schemas;

use App\Models\WcFixture;
use App\Models\WcGoal;
use App\Models\WcPlayer;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                            ->preload()
                            ->live(),
                        Select::make('away_team_id')
                            ->label('Away team')
                            ->relationship('awayTeam', 'name')
                            ->searchable()
                            ->preload()
                            ->live(),
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
                    ->description('Add each scorer. Saving rebuilds this fixture\'s goal records.')
                    // Hidden on create — there are no teams/players to pick from until the fixture exists.
                    ->visible(fn (?WcFixture $record): bool => $record !== null)
                    ->schema([
                        Repeater::make('goals')
                            ->label('Goalscorers')
                            ->addActionLabel('+ Add goalscorer')
                            ->defaultItems(0)
                            ->columns(12)
                            ->dehydrated(false)
                            ->schema([
                                Select::make('playerID')
                                    ->label('Player')
                                    ->options(fn (?WcFixture $record): array => self::playerOptions($record))
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(7),
                                TextInput::make('count')
                                    ->label('Goals')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required()
                                    ->columnSpan(3),
                                Toggle::make('is_own_goal')
                                    ->label('Own goal')
                                    ->columnSpan(2),
                            ]),
                    ]),
            ]);
    }

    /**
     * Active players of both teams in this fixture, keyed by playerID.
     * Label: "Flag #shirt Name".
     */
    public static function playerOptions(?WcFixture $record): array
    {
        if (! $record) {
            return [];
        }

        $teamIds = array_filter([$record->home_team_id, $record->away_team_id]);

        // Players already recorded as scorers in this fixture — included even if
        // inactive or on a since-changed team, so synced goals always render
        // with a proper player label in the repeater (not a blank Select).
        $scorerIds = WcGoal::where('fixtureID', $record->fixtureID)
            ->pluck('playerID')
            ->filter()
            ->unique();

        if (empty($teamIds) && $scorerIds->isEmpty()) {
            return [];
        }

        return WcPlayer::query()
            ->with('team')
            ->where(function ($q) use ($teamIds, $scorerIds) {
                if (! empty($teamIds)) {
                    $q->where(fn ($q2) => $q2->whereIn('teamID', $teamIds)->where('is_active', true));
                }
                if ($scorerIds->isNotEmpty()) {
                    $q->orWhereIn('playerID', $scorerIds);
                }
            })
            ->orderBy('teamID')
            ->orderBy('shirt_number')
            ->get()
            ->mapWithKeys(fn (WcPlayer $p) => [
                $p->playerID => trim(
                    ($p->team?->flag ? $p->team->flag . ' ' : '')
                    . ($p->shirt_number ? '#' . $p->shirt_number . ' ' : '')
                    . $p->name
                ),
            ])
            ->all();
    }
}
