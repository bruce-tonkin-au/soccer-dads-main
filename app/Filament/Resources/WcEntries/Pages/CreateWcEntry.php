<?php

namespace App\Filament\Resources\WcEntries\Pages;

use App\Filament\Resources\WcEntries\WcEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWcEntry extends CreateRecord
{
    protected static string $resource = WcEntryResource::class;

    protected function afterCreate(): void
    {
        WcEntryResource::syncDraw($this->record, $this->data);
    }
}
