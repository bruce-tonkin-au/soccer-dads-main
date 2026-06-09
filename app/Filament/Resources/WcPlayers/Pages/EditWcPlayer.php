<?php

namespace App\Filament\Resources\WcPlayers\Pages;

use App\Filament\Resources\WcPlayers\WcPlayerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWcPlayer extends EditRecord
{
    protected static string $resource = WcPlayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
