<?php

namespace App\Filament\Admin\Resources\Seasons;

use App\Filament\Admin\Resources\Seasons\Pages\CreateSeason;
use App\Filament\Admin\Resources\Seasons\Pages\EditSeason;
use App\Filament\Admin\Resources\Seasons\Pages\ListSeasons;
use App\Filament\Admin\Resources\Seasons\Schemas\SeasonForm;
use App\Filament\Admin\Resources\Seasons\Tables\SeasonsTable;
use App\Models\Season;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SeasonResource extends Resource
{
    protected static ?string $model = Season::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'seasonName';

    protected static ?string $modelLabel = 'season';

    protected static ?string $pluralModelLabel = 'seasons';

    protected static ?string $navigationLabel = 'Seasons';

    protected static ?string $slug = 'seasons';

    public static function form(Schema $schema): Schema
    {
        return SeasonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeasonsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeasons::route('/'),
            'create' => CreateSeason::route('/create'),
            'edit' => EditSeason::route('/{record}/edit'),
        ];
    }
}
