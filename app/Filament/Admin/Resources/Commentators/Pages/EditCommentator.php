<?php

namespace App\Filament\Admin\Resources\Commentators\Pages;

use App\Filament\Admin\Resources\Commentators\CommentatorResource;
use App\Models\Commentator;
use Filament\Resources\Pages\EditRecord;

class EditCommentator extends EditRecord
{
    protected static string $resource = CommentatorResource::class;

    // Enforce the single-default invariant — only one commentator may have
    // commentatorDefault = 1 (mirrors AdminController::updateCommentator).
    protected function afterSave(): void
    {
        if ($this->record->commentatorDefault) {
            Commentator::where('commentatorID', '!=', $this->record->commentatorID)
                ->update(['commentatorDefault' => false]);
        }
    }
}
