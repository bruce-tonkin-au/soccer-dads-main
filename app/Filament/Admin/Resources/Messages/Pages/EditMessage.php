<?php

namespace App\Filament\Admin\Resources\Messages\Pages;

use App\Filament\Admin\Resources\Messages\MessageResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\View\View;

class EditMessage extends EditRecord
{
    protected static string $resource = MessageResource::class;

    // Read-only distribution links (mirrors the legacy /links page). These only
    // make sense once a messageCode exists, so they live on the edit page only.
    // No send, no persistence.
    protected function getHeaderActions(): array
    {
        return [
            Action::make('links')
                ->label('Distribution links')
                ->icon('heroicon-o-link')
                ->color('gray')
                ->modalHeading('Distribution links')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalContent(fn (): View => view(
                    'filament.admin.messages.links',
                    ['message' => $this->record],
                )),
        ];
    }
}
