<?php

namespace App\Filament\Admin\Resources\Commentators;

use App\Filament\Admin\Resources\Commentators\Pages\CreateCommentator;
use App\Filament\Admin\Resources\Commentators\Pages\EditCommentator;
use App\Filament\Admin\Resources\Commentators\Pages\ListCommentators;
use App\Filament\Admin\Resources\Commentators\Schemas\CommentatorForm;
use App\Filament\Admin\Resources\Commentators\Tables\CommentatorsTable;
use App\Models\Commentator;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CommentatorResource extends Resource
{
    protected static ?string $model = Commentator::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-microphone';

    protected static string|UnitEnum|null $navigationGroup = 'Play';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'commentatorNameFirst';

    protected static ?string $modelLabel = 'commentator';

    protected static ?string $pluralModelLabel = 'commentators';

    protected static ?string $navigationLabel = 'Commentators';

    protected static ?string $slug = 'commentators';

    public static function form(Schema $schema): Schema
    {
        return CommentatorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommentatorsTable::configure($table);
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
            'index' => ListCommentators::route('/'),
            'create' => CreateCommentator::route('/create'),
            'edit' => EditCommentator::route('/{record}/edit'),
        ];
    }
}
