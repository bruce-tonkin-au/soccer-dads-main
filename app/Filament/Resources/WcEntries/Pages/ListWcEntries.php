<?php

namespace App\Filament\Resources\WcEntries\Pages;

use App\Filament\Resources\WcEntries\WcEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWcEntries extends ListRecords
{
    protected static string $resource = WcEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
