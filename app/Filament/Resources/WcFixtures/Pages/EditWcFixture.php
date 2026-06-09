<?php

namespace App\Filament\Resources\WcFixtures\Pages;

use App\Filament\Resources\WcFixtures\WcFixtureResource;
use App\Models\WcGoal;
use App\Models\WcPlayer;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

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
     * Pre-tick the goalscorer/own-goal fields from existing wc_goals rows.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $goals = WcGoal::where('fixtureID', $this->record->fixtureID)->get();

        $data['goalscorers'] = $goals->where('is_own_goal', false)->pluck('playerID')->all();
        $data['own_goals'] = $goals->where('is_own_goal', true)->pluck('playerID')->all();

        return $data;
    }

    /**
     * Sync wc_goals to match the ticked players. One goal per player per type
     * (own / not own); ticking inserts, unticking deletes. No minute is recorded.
     */
    protected function afterSave(): void
    {
        $fixtureID = $this->record->fixtureID;

        $normal = collect($this->data['goalscorers'] ?? [])->map(fn ($id) => (int) $id);
        $own = collect($this->data['own_goals'] ?? [])->map(fn ($id) => (int) $id);

        // Desired keys "playerID|isOwnGoal"
        $desired = $normal->map(fn ($id) => $id . '|0')
            ->merge($own->map(fn ($id) => $id . '|1'))
            ->unique();

        $existing = WcGoal::where('fixtureID', $fixtureID)->get();

        // Delete goals that are no longer ticked.
        foreach ($existing as $goal) {
            $key = $goal->playerID . '|' . ($goal->is_own_goal ? '1' : '0');
            if (! $desired->contains($key)) {
                $goal->delete();
            }
        }

        $existingKeys = $existing->map(fn ($g) => $g->playerID . '|' . ($g->is_own_goal ? '1' : '0'));

        // Look up each player's team for the teamID column.
        $teamByPlayer = WcPlayer::whereIn('playerID', $normal->merge($own)->unique())
            ->pluck('teamID', 'playerID');

        foreach ($desired as $key) {
            if ($existingKeys->contains($key)) {
                continue;
            }

            [$playerID, $isOwn] = explode('|', $key);
            $playerID = (int) $playerID;

            WcGoal::create([
                'fixtureID' => $fixtureID,
                'playerID' => $playerID,
                'teamID' => $teamByPlayer[$playerID] ?? null,
                'minute' => null,
                'is_own_goal' => $isOwn === '1',
            ]);
        }
    }
}
