<?php

namespace App\Filament\Admin\Resources\Messages\Pages;

use App\Filament\Admin\Resources\Messages\MessageResource;
use App\Models\Message;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateMessage extends CreateRecord
{
    protected static string $resource = MessageResource::class;

    // Mirror AdminController::storeMessage — generate a unique random 8-char
    // uppercase code (the column is UNIQUE and not user-entered) and default
    // the message to active. messageCode is never exposed as an editable field.
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Message::where('messageCode', $code)->exists());

        $data['messageCode'] = $code;

        if (! isset($data['messageActive']) || $data['messageActive'] === '' || $data['messageActive'] === null) {
            $data['messageActive'] = 1;
        }

        return $data;
    }
}
