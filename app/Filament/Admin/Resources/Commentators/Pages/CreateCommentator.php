<?php

namespace App\Filament\Admin\Resources\Commentators\Pages;

use App\Filament\Admin\Resources\Commentators\CommentatorResource;
use App\Models\Commentator;
use Filament\Resources\Pages\CreateRecord;

class CreateCommentator extends CreateRecord
{
    protected static string $resource = CommentatorResource::class;

    // Enforce the single-default invariant — only one commentator may have
    // commentatorDefault = 1 (AdminController::storeCommentator does the same;
    // line 495 relies on where('commentatorDefault', 1) returning one row).
    protected function afterCreate(): void
    {
        if ($this->record->commentatorDefault) {
            Commentator::where('commentatorID', '!=', $this->record->commentatorID)
                ->update(['commentatorDefault' => false]);
        }
    }
}
