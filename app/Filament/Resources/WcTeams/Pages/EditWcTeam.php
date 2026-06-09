<?php

namespace App\Filament\Resources\WcTeams\Pages;

use App\Filament\Resources\WcTeams\WcTeamResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWcTeam extends EditRecord
{
    protected static string $resource = WcTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
