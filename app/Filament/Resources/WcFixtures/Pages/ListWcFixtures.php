<?php

namespace App\Filament\Resources\WcFixtures\Pages;

use App\Filament\Resources\WcFixtures\WcFixtureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWcFixtures extends ListRecords
{
    protected static string $resource = WcFixtureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
