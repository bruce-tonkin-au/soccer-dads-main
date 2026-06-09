<?php

namespace App\Filament\Resources\WcFixtures;

use App\Filament\Resources\WcFixtures\Pages\CreateWcFixture;
use App\Filament\Resources\WcFixtures\Pages\EditWcFixture;
use App\Filament\Resources\WcFixtures\Pages\ListWcFixtures;
use App\Filament\Resources\WcFixtures\Schemas\WcFixtureForm;
use App\Filament\Resources\WcFixtures\Tables\WcFixturesTable;
use App\Models\WcFixture;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class WcFixtureResource extends Resource
{
    protected static ?string $model = WcFixture::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static string|UnitEnum|null $navigationGroup = 'World Cup 2026';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'fixture';

    public static function form(Schema $schema): Schema
    {
        return WcFixtureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WcFixturesTable::configure($table);
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
            'index' => ListWcFixtures::route('/'),
            'create' => CreateWcFixture::route('/create'),
            'edit' => EditWcFixture::route('/{record}/edit'),
        ];
    }
}
