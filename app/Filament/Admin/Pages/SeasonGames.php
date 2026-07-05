<?php

namespace App\Filament\Admin\Pages;

use App\Models\Commentator;
use App\Services\SeasonLadderService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;

/**
 * Custom per-season games screen for /manage — mirrors the legacy
 * /admin/seasons/{seasonID}/games screen (game list + CRUD + ladder + chart).
 * Reached from a "Games" row action on the Seasons resource; not in the nav.
 *
 * Teams and Print link out to the legacy /admin screens (not yet migrated).
 * Registrations links to the /manage Registrations page. Charging is
 * indicator-only here; "Charge players" links out to the legacy /admin games
 * screen where the charge modal lives (the charge flow is not ported yet).
 */
class SeasonGames extends Page
{
    protected string $view = 'filament.admin.pages.season-games';

    protected static ?string $slug = 'season-games';

    // Reached from the Seasons resource, not the sidebar.
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    // Which season this page is showing — shareable via the URL, exactly like
    // the Registrations page's gameID param.
    #[Url]
    public ?int $seasonID = null;

    // Game currently open in the Edit modal; set when the action mounts so the
    // form-data / bibs-washer option closures can read it.
    public ?int $editingGameID = null;

    protected ?object $seasonCache = null;

    public function mount(): void
    {
        if (! $this->seasonID) {
            abort(404);
        }

        // firstOrFail → 404 for an unknown season.
        $this->season();
    }

    public function getTitle(): string
    {
        return $this->season()->seasonName . ' — Games';
    }

    protected function season(): object
    {
        if ($this->seasonCache === null) {
            $this->seasonCache = DB::table('seasons')->where('seasonID', $this->seasonID)->firstOrFail();
        }

        return $this->seasonCache;
    }

    /**
     * Re-run on every render (including after each Livewire action), so the
     * games table, charge indicators and ladder always reflect current state.
     * The gamesWithTeams / chargedGameIDs existence checks are moved here from
     * the legacy AdminController::games().
     */
    protected function getViewData(): array
    {
        $seasonID = $this->seasonID;

        $season = $this->season();
        $games  = DB::table('games')->where('gameSeasonID', $seasonID)->orderBy('gameRound')->get();

        $chargeableIDs = $games->where('gameDate', '>=', '2026-05-01')->pluck('gameID');

        $gamesWithTeams = $chargeableIDs->isNotEmpty()
            ? DB::table('scoring-teams-players as stp')
                ->join('scoring-teams as st', 'stp.teamID', '=', 'st.teamsID')
                ->whereIn('st.gameID', $chargeableIDs)
                ->where('stp.playerActive', 1)
                ->distinct()
                ->pluck('st.gameID')
            : collect();

        $chargedGameIDs = $chargeableIDs->isNotEmpty()
            ? DB::table('account')
                ->whereIn('gameID', $chargeableIDs)
                ->whereNotNull('gameID')
                ->distinct()
                ->pluck('gameID')
            : collect();

        $ladderData = app(SeasonLadderService::class)->buildSeasonLadder($seasonID, $games);

        return array_merge(
            compact('season', 'seasonID', 'games', 'gamesWithTeams', 'chargedGameIDs'),
            $ladderData
        );
    }

    // ── Add game ── mirrors AdminController::storeGame, including the
    // scoring-settings side-effect (new games break scoring without it).
    public function createGameAction(): Action
    {
        return Action::make('createGame')
            ->label('Add game')
            ->icon('heroicon-o-plus')
            ->modalHeading('Add game')
            ->schema([
                TextInput::make('gameRound')
                    ->label('Round number')
                    ->numeric()
                    ->required(),
                DatePicker::make('gameDate')
                    ->label('Date')
                    ->native(false)
                    ->required(),
                TextInput::make('gameYouTube')
                    ->label('YouTube URL (optional)')
                    ->maxLength(255)
                    ->placeholder('https://www.youtube.com/watch?v=...'),
                Select::make('gameVisible')
                    ->label('Visible')
                    ->options([1 => 'Yes', 0 => 'No'])
                    ->default(1)
                    ->required()
                    ->selectablePlaceholder(false),
            ])
            ->action(function (array $data): void {
                do {
                    $gameCode = strtoupper(Str::random(4));
                } while (DB::table('games')->where('gameCode', $gameCode)->exists());

                $gameID = DB::table('games')->insertGetId([
                    'gameSeasonID' => $this->seasonID,
                    'gameRound'    => $data['gameRound'],
                    'gameDate'     => $data['gameDate'],
                    'gameYouTube'  => $data['gameYouTube'] ?? null,
                    'gameVisible'  => $data['gameVisible'] ?? 1,
                    'gameCode'     => $gameCode,
                ], 'gameID');

                // Same scoring-settings defaults as AdminController::storeGame.
                DB::table('scoring-settings')->insert([
                    'gameID'                => $gameID,
                    'settingsRounds'        => 7,
                    'settingsGamesPerRound' => 3,
                    'settingsGameDuration'  => 60,
                    'settingsTeams'         => 3,
                    'teamAID'               => null,
                    'teamBID'               => null,
                    'teamCID'               => null,
                    'settingsPointsWin'     => 2,
                    'settingsPointsDraw'    => 1,
                    'commentatorID'         => Commentator::where('commentatorDefault', 1)->value('commentatorID')
                        ?? Commentator::orderBy('commentatorID')->value('commentatorID'),
                    'settingsActive'        => 1,
                    'settingsVisible'       => 1,
                ]);

                Notification::make()->title('Game created.')->success()->send();
            });
    }

    // ── Edit game ── superset form; mirrors AdminController::updateGame,
    // including the gameCode uniqueness check and the attendee bibs-washer list.
    public function editGameAction(): Action
    {
        return Action::make('editGame')
            ->label('Edit')
            ->icon('heroicon-o-pencil')
            ->color('gray')
            ->modalHeading('Edit game')
            ->mountUsing(function (array $arguments): void {
                $this->editingGameID = (int) $arguments['gameID'];
            })
            ->fillForm(fn (): array => $this->gameEditFormData())
            ->schema([
                TextInput::make('gameCode')
                    ->label('Game code')
                    ->maxLength(4)
                    ->placeholder('e.g. A3K9')
                    // Uniqueness against every OTHER game, like updateGame.
                    ->rule(fn (): \Closure => function (string $attribute, $value, \Closure $fail): void {
                        $code = strtoupper(trim((string) $value)) ?: null;
                        if ($code && DB::table('games')
                            ->where('gameCode', $code)
                            ->where('gameID', '!=', $this->editingGameID)
                            ->exists()) {
                            $fail('This game code is already in use by another game.');
                        }
                    }),
                TextInput::make('gameRound')
                    ->label('Round number')
                    ->numeric()
                    ->required(),
                DatePicker::make('gameDate')
                    ->label('Date')
                    ->native(false),
                TextInput::make('gameYouTube')
                    ->label('YouTube URL')
                    ->maxLength(255),
                DateTimePicker::make('gameYouTubeStart')
                    ->label('YouTube start time')
                    ->seconds(true)
                    ->native(false)
                    ->helperText('Enter Adelaide local time.'),
                Select::make('gameVisible')
                    ->label('Visible')
                    ->options([1 => 'Yes', 0 => 'No'])
                    ->required()
                    ->selectablePlaceholder(false),
                Select::make('gameBibsMemberID')
                    ->label('Bibs washer')
                    ->options(fn (): array => $this->bibsWasherOptions())
                    ->placeholder('— Not assigned —'),
            ])
            ->action(function (array $data): void {
                $gameID   = $this->editingGameID;
                $gameCode = strtoupper(trim($data['gameCode'] ?? '')) ?: null;

                DB::table('games')->where('gameID', $gameID)->update([
                    'gameRound'        => $data['gameRound'],
                    'gameDate'         => $data['gameDate'],
                    'gameYouTube'      => $data['gameYouTube'] ?? null,
                    'gameYouTubeStart' => $data['gameYouTubeStart'] ?: null,
                    'gameVisible'      => $data['gameVisible'] ?? 1,
                    'gameCode'         => $gameCode,
                    'gameBibsMemberID' => $data['gameBibsMemberID'] ?: null,
                ]);

                Notification::make()->title('Game updated.')->success()->send();
            });
    }

    protected function gameEditFormData(): array
    {
        $game = DB::table('games')->where('gameID', $this->editingGameID)->firstOrFail();

        return [
            'gameCode'         => $game->gameCode,
            'gameRound'        => $game->gameRound,
            'gameDate'         => $game->gameDate ? Carbon::parse($game->gameDate)->format('Y-m-d') : null,
            'gameYouTube'      => $game->gameYouTube,
            'gameYouTubeStart' => $game->gameYouTubeStart ? Carbon::parse($game->gameYouTubeStart)->format('Y-m-d H:i:s') : null,
            'gameVisible'      => (int) $game->gameVisible,
            'gameBibsMemberID' => $game->gameBibsMemberID,
        ];
    }

    // Attendees for the edited game (active results) + the current bibs washer,
    // mirroring AdminController::editGame.
    protected function bibsWasherOptions(): array
    {
        $game = DB::table('games')->where('gameID', $this->editingGameID)->first();

        $attendeeIDs = DB::table('results')
            ->where('resultGameID', $this->editingGameID)
            ->where('resultActive', 1)
            ->pluck('resultMemberID')
            ->unique();

        if ($game && $game->gameBibsMemberID) {
            $attendeeIDs = $attendeeIDs->push($game->gameBibsMemberID)->unique();
        }

        return DB::table('members')
            ->whereIn('memberID', $attendeeIDs)
            ->orderBy('memberNameLast')
            ->orderBy('memberNameFirst')
            ->get()
            ->mapWithKeys(fn ($m): array => [
                $m->memberID => $m->memberNameLast . ', ' . $m->memberNameFirst,
            ])
            ->all();
    }
}
