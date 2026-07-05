<?php

namespace App\Filament\Admin\Resources\Seasons\Pages;

use App\Filament\Admin\Resources\Seasons\SeasonResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditSeason extends EditRecord
{
    protected static string $resource = SeasonResource::class;

    // Places live in season-awards (awardPlayer1/2/3), not on the seasons row,
    // so they can't be plain model-bound fields. Stashed here between
    // mutateFormDataBeforeSave() and afterSave().
    protected ?array $awardData = null;

    // LOAD: hydrate the three place pickers from the season's season-awards row.
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $award = DB::table('season-awards')
            ->where('seasonID', $this->record->seasonID)
            ->where('awardActive', 1)
            ->first();

        $data['award1'] = $award->awardPlayer1 ?? null;
        $data['award2'] = $award->awardPlayer2 ?? null;
        $data['award3'] = $award->awardPlayer3 ?? null;

        return $data;
    }

    // Pull the three place fields out of the data before the model is saved —
    // they are not seasons columns. Persisted to season-awards in afterSave().
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->awardData = [
            'awardPlayer1' => $data['award1'] ?? null,
            'awardPlayer2' => $data['award2'] ?? null,
            'awardPlayer3' => $data['award3'] ?? null,
        ];

        unset($data['award1'], $data['award2'], $data['award3']);

        return $data;
    }

    // SAVE: upsert the season's season-awards row (create if none, keeping
    // awardActive = 1). Clearing all three to null is allowed.
    protected function afterSave(): void
    {
        if ($this->awardData === null) {
            return;
        }

        $existing = DB::table('season-awards')
            ->where('seasonID', $this->record->seasonID)
            ->where('awardActive', 1)
            ->first();

        if ($existing) {
            DB::table('season-awards')
                ->where('awardID', $existing->awardID)
                ->update([
                    'awardPlayer1' => $this->awardData['awardPlayer1'],
                    'awardPlayer2' => $this->awardData['awardPlayer2'],
                    'awardPlayer3' => $this->awardData['awardPlayer3'],
                    'awardEdited'  => now(),
                ]);
        } else {
            DB::table('season-awards')->insert([
                'seasonID'     => $this->record->seasonID,
                'awardPlayer1' => $this->awardData['awardPlayer1'],
                'awardPlayer2' => $this->awardData['awardPlayer2'],
                'awardPlayer3' => $this->awardData['awardPlayer3'],
                'awardActive'  => 1,
                'awardVisible' => 1,
                'awardCreated' => now(),
                'awardEdited'  => now(),
            ]);
        }
    }
}
