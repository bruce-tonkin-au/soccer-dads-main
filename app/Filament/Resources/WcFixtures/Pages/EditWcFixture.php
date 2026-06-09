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
     * (player, own-goal) pair, with the goal count.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['goals'] = WcGoal::where('fixtureID', $this->record->fixtureID)
            ->get()
            ->groupBy(fn (WcGoal $goal) => $goal->playerID . '|' . ($goal->is_own_goal ? '1' : '0'))
            ->map(fn ($group) => [
                'playerID' => $group->first()->playerID,
                'count' => $group->count(),
                'is_own_goal' => (bool) $group->first()->is_own_goal,
            ])
            ->values()
            ->all();

        return $data;
    }

    /**
     * Rebuild wc_goals from the repeater: clear the fixture's goals, then
     * insert one row per goal (a count of 2 => 2 rows). teamID comes from the
     * player's team; no minute is recorded.
     */
    protected function afterSave(): void
    {
        $fixtureID = $this->record->fixtureID;
        $rows = collect($this->data['goals'] ?? []);

        $teamByPlayer = WcPlayer::whereIn('playerID', $rows->pluck('playerID')->filter()->unique())
            ->pluck('teamID', 'playerID');

        DB::transaction(function () use ($fixtureID, $rows, $teamByPlayer) {
            WcGoal::where('fixtureID', $fixtureID)->delete();

            foreach ($rows as $row) {
                $playerID = (int) ($row['playerID'] ?? 0);
                if ($playerID === 0) {
                    continue;
                }

                $count = max(1, (int) ($row['count'] ?? 1));
                $isOwnGoal = (bool) ($row['is_own_goal'] ?? false);

                for ($i = 0; $i < $count; $i++) {
                    WcGoal::create([
                        'fixtureID' => $fixtureID,
                        'playerID' => $playerID,
                        'teamID' => $teamByPlayer[$playerID] ?? null,
                        'minute' => null,
                        'is_own_goal' => $isOwnGoal,
                    ]);
                }
            }
        });
    }
}
