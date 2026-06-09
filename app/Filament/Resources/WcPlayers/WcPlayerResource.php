<?php

namespace App\Filament\Resources\WcPlayers;

use App\Filament\Resources\WcPlayers\Pages\CreateWcPlayer;
use App\Filament\Resources\WcPlayers\Pages\EditWcPlayer;
use App\Filament\Resources\WcPlayers\Pages\ListWcPlayers;
use App\Filament\Resources\WcPlayers\Schemas\WcPlayerForm;
use App\Filament\Resources\WcPlayers\Tables\WcPlayersTable;
use App\Models\WcPlayer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class WcPlayerResource extends Resource
{
    protected static ?string $model = WcPlayer::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|UnitEnum|null $navigationGroup = 'World Cup 2026';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'player';

    public static function form(Schema $schema): Schema
    {
        return WcPlayerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WcPlayersTable::configure($table);
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
            'index' => ListWcPlayers::route('/'),
            'create' => CreateWcPlayer::route('/create'),
            'edit' => EditWcPlayer::route('/{record}/edit'),
        ];
    }
}
