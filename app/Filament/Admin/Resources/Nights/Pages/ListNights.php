<?php

namespace App\Filament\Admin\Resources\Nights\Pages;

use App\Filament\Admin\Resources\Nights\NightResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNights extends ListRecords
{
    protected static string $resource = NightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
