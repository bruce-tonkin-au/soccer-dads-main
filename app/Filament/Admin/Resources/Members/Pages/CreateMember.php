<?php

namespace App\Filament\Admin\Resources\Members\Pages;

use App\Filament\Admin\Resources\Members\MemberResource;
use App\Support\MemberIdentifier;
use Filament\Resources\Pages\CreateRecord;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $firstName = trim($data['memberNameFirst'] ?? '');
        $lastName  = trim($data['memberNameLast'] ?? '');

        $data['memberCode'] = MemberIdentifier::generateCode();
        $data['memberSlug'] = MemberIdentifier::generateSlug($firstName, $lastName);

        if (! isset($data['memberActive']) || $data['memberActive'] === '' || $data['memberActive'] === null) {
            $data['memberActive'] = 1;
        }

        return $data;
    }
}
