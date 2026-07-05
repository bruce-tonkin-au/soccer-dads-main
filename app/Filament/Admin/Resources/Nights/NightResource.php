<?php

namespace App\Filament\Admin\Resources\Nights;

use App\Filament\Admin\Resources\Nights\Pages\CreateNights;
use App\Filament\Admin\Resources\Nights\Pages\EditNights;
use App\Filament\Admin\Resources\Nights\Pages\ListNights;
use App\Filament\Admin\Resources\Nights\Schemas\NightForm;
use App\Filament\Admin\Resources\Nights\Tables\NightsTable;
use App\Models\Night;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class NightResource extends Resource
{
    protected static ?string $model = Night::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-moon';

    protected static string|UnitEnum|null $navigationGroup = 'Play';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nightName';

    protected static ?string $modelLabel = 'night';

    protected static ?string $pluralModelLabel = 'nights';

    protected static ?string $navigationLabel = 'Nights';

    protected static ?string $slug = 'nights';

    public static function form(Schema $schema): Schema
    {
        return NightForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NightsTable::configure($table);
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
            'index' => ListNights::route('/'),
            'create' => CreateNights::route('/create'),
            'edit' => EditNights::route('/{record}/edit'),
        ];
    }
}
