<?php

namespace App\Filament\Resources\WcPlayers\Pages;

use App\Filament\Resources\WcPlayers\WcPlayerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWcPlayers extends ListRecords
{
    protected static string $resource = WcPlayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
