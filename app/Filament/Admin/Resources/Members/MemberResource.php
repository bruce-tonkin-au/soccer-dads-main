<?php

namespace App\Filament\Admin\Resources\Members;

use App\Filament\Admin\Resources\Members\Pages\CreateMember;
use App\Filament\Admin\Resources\Members\Pages\EditMember;
use App\Filament\Admin\Resources\Members\Pages\ListMembers;
use App\Filament\Admin\Resources\Members\Schemas\MemberForm;
use App\Filament\Admin\Resources\Members\Tables\MembersTable;
use App\Models\Member;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'memberNameLast';

    protected static ?string $modelLabel = 'player';

    protected static ?string $pluralModelLabel = 'players';

    protected static ?string $navigationLabel = 'Players';

    protected static ?string $slug = 'players';

    public static function form(Schema $schema): Schema
    {
        return MemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MembersTable::configure($table);
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
            'index' => ListMembers::route('/'),
            'create' => CreateMember::route('/create'),
            'edit' => EditMember::route('/{record}/edit'),
        ];
    }
}
