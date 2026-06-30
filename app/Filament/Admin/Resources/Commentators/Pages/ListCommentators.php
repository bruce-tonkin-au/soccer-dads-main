<?php

namespace App\Filament\Admin\Resources\Commentators\Pages;

use App\Filament\Admin\Resources\Commentators\CommentatorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommentators extends ListRecords
{
    protected static string $resource = CommentatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
