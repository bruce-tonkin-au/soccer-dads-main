<?php

namespace App\Filament\Admin\Resources\Ratings\Pages;

use App\Filament\Admin\Resources\Ratings\RatingResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListRatings extends ListRecords
{
    protected static string $resource = RatingResource::class;

    // The summary table is a grouped aggregate (one row per rated player),
    // so the model's own key (ratingID) is not selected — key rows by the
    // ratedMemberID exposed by the grouped query instead.
    public function getTableRecordKey(Model | array $record): string
    {
        return (string) ($record->ratedMemberID ?? '');
    }

    // Read-only: no header create action.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
