<?php

namespace App\Filament\Admin\Widgets;

use App\Services\RegistrationActions;
use App\Services\RegistrationsQuery;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NextGamePanel extends Widget
{
    protected string $view = 'filament.admin.widgets.next-game-panel';

    protected int|string|array $columnSpan = 'full';

    // ── Chip actions — delegate to the shared RegistrationActions service, then
    //    the widget re-renders (getViewData re-runs) so the lists refresh. Same
    //    methods the Registrations page + legacy dashboard endpoints use, so the
    //    event logging + sequence stay correct. No reg logic is reimplemented.

    public function register(int $memberID): void
    {
        $game = $this->nextGame();
        if (! $game) {
            return;
        }
        app(RegistrationActions::class)->register($game->gameID, $memberID);
        Notification::make()->title('Player registered.')->success()->send();
    }

    public function deregister(int $memberID): void
    {
        $game = $this->nextGame();
        if (! $game) {
            return;
        }
        app(RegistrationActions::class)->deregister($game->gameID, $memberID);
        Notification::make()->title('Player marked as not going.')->success()->send();
    }

    // Mirrors AdminController::resetNight — clears the game-night start time.
    public function resetNight(): void
    {
        $game = $this->nextGame();
        if (! $game) {
            return;
        }
        DB::table('games')->where('gameID', $game->gameID)->update(['gameStartTime' => null]);
        Notification::make()->title('Game night start time has been reset.')->success()->send();
    }

    private function nextGame(): ?object
    {
        return app(RegistrationsQuery::class)->data(null)['nextGame'] ?? null;
    }

    protected function getViewData(): array
    {
        // Reuse the shared query: resolves currentSeason → next visible game and
        // returns that game's registrations. Only render when there is an actual
        // upcoming game (mirrors the legacy @if($nextGame) gate; data()'s fallback
        // to a past game is ignored here).
        $data = app(RegistrationsQuery::class)->data(null);
        $nextGame = $data['nextGame'];

        if (! $nextGame) {
            return ['nextGame' => null];
        }

        // When nextGame exists, data()['game'] is that same game but joined to
        // its season (has seasonName + gameSeasonID for the header/links).
        $game          = $data['game'];
        $registrations = $data['registrations'];

        return [
            'nextGame'     => $nextGame,
            'game'         => $game,
            'playersCount' => DB::table('members')->count(),
            'active'       => $registrations->where('registrationStatus', 1)->where('registrationBench', 0)->values(),
            'bench'        => $registrations->where('registrationStatus', 1)->where('registrationBench', 1)->values(),
            'notGoing'     => $registrations->where('registrationStatus', 2)->values(),
            'recent'       => $this->recentUnregistered($nextGame, $registrations),
        ];
    }

    /**
     * Active members who played the LAST 5 COMPLETED games but aren't registered
     * for the next game. This deliberately preserves the LEGACY dashboard
     * semantics ("played recently" = last 5 completed games), which differ from
     * RegistrationsQuery::data()['unregistered'] ("played this season"). Kept as a
     * small local helper so the dashboard's meaning isn't silently changed — a
     * verbatim port of AdminController::dashboard()'s recentUnregistered block.
     */
    private function recentUnregistered(object $nextGame, Collection $registrations): Collection
    {
        $recentGameIds = DB::table('games')
            ->where('gameVisible', 1)
            ->where('gameID', '<', $nextGame->gameID)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))->from('scoring')
                  ->whereColumn('scoring.gameID', 'games.gameID')
                  ->whereNotNull('scoring.scoringEnded');
            })
            ->orderBy('gameID', 'desc')
            ->limit(5)
            ->pluck('gameID');

        if ($recentGameIds->isEmpty()) {
            return collect();
        }

        $registeredIds = $registrations->pluck('memberID')->unique();

        return DB::table('results as r')
            ->join('members as m', 'r.resultMemberID', '=', 'm.memberID')
            ->where('r.resultActive', 1)
            ->where('m.memberActive', 1)
            ->whereIn('r.resultGameID', $recentGameIds)
            ->when($registeredIds->isNotEmpty(), fn ($q) => $q->whereNotIn('m.memberID', $registeredIds))
            ->orderBy('m.memberNameLast')
            ->select('m.memberID', 'm.memberNameFirst', 'm.memberNameLast', 'm.memberSlug')
            ->distinct()
            ->get();
    }
}
