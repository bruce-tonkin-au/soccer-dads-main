<?php

namespace App\Filament\Admin\Resources\Members\Pages;

use App\Filament\Admin\Resources\Members\MemberResource;
use App\Support\MemberIdentifier;
use Filament\Resources\Pages\EditRecord;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $firstName = trim($data['memberNameFirst'] ?? '');
        $lastName  = trim($data['memberNameLast'] ?? '');

        // Backfill identifiers only when the existing record is missing them,
        // matching AdminController::updatePlayer.
        if (empty($this->record->memberCode)) {
            $data['memberCode'] = MemberIdentifier::generateCode();
        }
        if (empty($this->record->memberSlug)) {
            $data['memberSlug'] = MemberIdentifier::generateSlug($firstName, $lastName, (int) $this->record->memberID);
        }

        return $data;
    }
}
