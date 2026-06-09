<?php

namespace App\Filament\Resources\WcTeams;

use App\Filament\Resources\WcTeams\Pages\CreateWcTeam;
use App\Filament\Resources\WcTeams\Pages\EditWcTeam;
use App\Filament\Resources\WcTeams\Pages\ListWcTeams;
use App\Filament\Resources\WcTeams\Schemas\WcTeamForm;
use App\Filament\Resources\WcTeams\Tables\WcTeamsTable;
use App\Models\WcTeam;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class WcTeamResource extends Resource
{
    protected static ?string $model = WcTeam::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-flag';

    protected static string|UnitEnum|null $navigationGroup = 'World Cup 2026';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'team';

    public static function form(Schema $schema): Schema
    {
        return WcTeamForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WcTeamsTable::configure($table);
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
            'index' => ListWcTeams::route('/'),
            'create' => CreateWcTeam::route('/create'),
            'edit' => EditWcTeam::route('/{record}/edit'),
        ];
    }
}
