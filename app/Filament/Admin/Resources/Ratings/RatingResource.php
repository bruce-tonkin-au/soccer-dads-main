<?php

namespace App\Filament\Admin\Resources\Ratings;

use App\Filament\Admin\Resources\Ratings\Pages\ListRatings;
use App\Filament\Admin\Resources\Ratings\Pages\PlayerRatingDetail;
use App\Filament\Admin\Resources\Ratings\Tables\RatingsTable;
use App\Models\PlayerRating;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class RatingResource extends Resource
{
    protected static ?string $model = PlayerRating::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static string|UnitEnum|null $navigationGroup = 'Members';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Ratings';

    protected static ?string $modelLabel = 'rating';

    protected static ?string $pluralModelLabel = 'ratings';

    protected static ?string $slug = 'ratings';

    public static function table(Table $table): Table
    {
        return RatingsTable::configure($table);
    }

    // Read-only reporting section — no create/edit pages, only the grouped
    // summary list and a per-player detail drill-down.
    public static function getPages(): array
    {
        return [
            'index' => ListRatings::route('/'),
            'player' => PlayerRatingDetail::route('/{ratedMemberID}'),
        ];
    }
}
