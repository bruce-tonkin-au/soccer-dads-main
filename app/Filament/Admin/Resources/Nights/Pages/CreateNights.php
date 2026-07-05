<?php

namespace App\Filament\Admin\Resources\Nights\Pages;

use App\Filament\Admin\Resources\Nights\NightResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNights extends CreateRecord
{
    protected static string $resource = NightResource::class;
}
