<?php

namespace App\Filament\Resources\WcFixtures\Pages;

use App\Filament\Resources\WcFixtures\WcFixtureResource;
use App\Models\WcGoal;
use App\Models\WcPlayer;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditWcFixture extends EditRecord
{
    protected static string $resource = WcFixtureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Collapse existing wc_goals into repeater rows: one row per distinct
     * (player, own-goal, shootout) triple, with the goal count. Shootout is
     * part of the key so a player's regular goal and shootout pen don't merge.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['goals'] = WcGoal::where('fixtureID', $this->record->fixtureID)
            ->get()
            ->groupBy(fn (WcGoal $goal) => $goal->playerID
                . '|' . ($goal->is_own_goal ? '1' : '0')
                . '|' . ($goal->is_shootout ? '1' : '0'))
            ->map(fn ($group) => [
                'playerID' => $group->first()->playerID,
                'count' => $group->count(),
                'is_own_goal' => (bool) $group->first()->is_own_goal,
                'is_shootout' => (bool) $group->first()->is_shootout,
            ])
            ->values()
            ->all();

        return $data;
    }

    /**
     * Rebuild wc_goals from the repeater: clear the fixture's goals, then
     * insert one row per goal (a count of 2 => 2 rows). teamID is the team
     * CREDITED with the goal — the scorer's team for a normal goal, or the
     * opponent in this fixture for an own goal (matching the auto-sync). No
     * minute is recorded.
     */
    protected function afterSave(): void
    {
        $fixtureID = $this->record->fixtureID;
        $homeTeamId = $this->record->home_team_id;
        $awayTeamId = $this->record->away_team_id;
        $rows = collect($this->data['goals'] ?? []);

        $teamByPlayer = WcPlayer::whereIn('playerID', $rows->pluck('playerID')->filter()->unique())
            ->pluck('teamID', 'playerID');

        DB::transaction(function () use ($fixtureID, $homeTeamId, $awayTeamId, $rows, $teamByPlayer) {
            WcGoal::where('fixtureID', $fixtureID)->delete();

            foreach ($rows as $row) {
                $playerID = (int) ($row['playerID'] ?? 0);
                if ($playerID === 0) {
                    continue;
                }

                $count = max(1, (int) ($row['count'] ?? 1));
                $isOwnGoal = (bool) ($row['is_own_goal'] ?? false);
                // is_shootout has no form control — it round-trips silently
                // through mutateFormDataBeforeFill so admin Save doesn't clear
                // the flag on shootout rows loaded from the API sync.
                $isShootout = (bool) ($row['is_shootout'] ?? false);
                $scorerTeamId = $teamByPlayer[$playerID] ?? null;

                // Own goals are credited to the OPPONENT (the benefiting side),
                // not the scorer's team — same rule as SyncsWcGoals.
                $teamID = $isOwnGoal
                    ? $this->benefitingTeamId($scorerTeamId, $homeTeamId, $awayTeamId)
                    : $scorerTeamId;

                for ($i = 0; $i < $count; $i++) {
                    WcGoal::create([
                        'fixtureID' => $fixtureID,
                        'playerID' => $playerID,
                        'teamID' => $teamID,
                        'minute' => null,
                        'is_own_goal' => $isOwnGoal,
                        'is_shootout' => $isShootout,
                    ]);
                }
            }
        });
    }

    /**
     * The team credited with an own goal scored by a player on $scorerTeamId:
     * the opponent in this fixture. Falls back to the scorer's team when it
     * sits on neither side (data anomaly).
     */
    protected function benefitingTeamId(?int $scorerTeamId, ?int $homeTeamId, ?int $awayTeamId): ?int
    {
        if ($scorerTeamId !== null && (int) $scorerTeamId === (int) $homeTeamId) {
            return $awayTeamId;
        }
        if ($scorerTeamId !== null && (int) $scorerTeamId === (int) $awayTeamId) {
            return $homeTeamId;
        }

        return $scorerTeamId;
    }
}
