<?php

namespace App\Filament\Resources\WcTeams\Pages;

use App\Filament\Resources\WcTeams\WcTeamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWcTeams extends ListRecords
{
    protected static string $resource = WcTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
