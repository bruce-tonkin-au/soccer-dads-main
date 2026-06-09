<?php

namespace App\Filament\Resources\WcEntries\Pages;

use App\Filament\Resources\WcEntries\WcEntryResource;
use App\Models\WcEntryPlayer;
use App\Models\WcEntryTeam;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWcEntry extends EditRecord
{
    protected static string $resource = WcEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Load the existing draw selections from the pivot tables into the form.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $entryID = $this->record->entryID;

        $data['tier1_team'] = WcEntryTeam::where('entryID', $entryID)->where('tier', 1)->value('teamID');
        $data['tier2_team'] = WcEntryTeam::where('entryID', $entryID)->where('tier', 2)->value('teamID');
        $data['player_slot1'] = WcEntryPlayer::where('entryID', $entryID)->where('slot', 1)->value('playerID');
        $data['player_slot2'] = WcEntryPlayer::where('entryID', $entryID)->where('slot', 2)->value('playerID');
        $data['player_slot3'] = WcEntryPlayer::where('entryID', $entryID)->where('slot', 3)->value('playerID');
        $data['player_slot4'] = WcEntryPlayer::where('entryID', $entryID)->where('slot', 4)->value('playerID');

        return $data;
    }

    protected function afterSave(): void
    {
        WcEntryResource::syncDraw($this->record, $this->data);
    }
}
