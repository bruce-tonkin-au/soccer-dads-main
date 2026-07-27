<?php

namespace App\Filament\Admin\Resources\News;

use App\Filament\Admin\Resources\News\Pages\CreateNews;
use App\Filament\Admin\Resources\News\Pages\EditNews;
use App\Filament\Admin\Resources\News\Pages\ListNews;
use App\Filament\Admin\Resources\News\Schemas\NewsForm;
use App\Filament\Admin\Resources\News\Tables\NewsTable;
use App\Models\News;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static string|UnitEnum|null $navigationGroup = 'Manage';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'newsTitle';

    protected static ?string $modelLabel = 'news item';

    protected static ?string $pluralModelLabel = 'news items';

    protected static ?string $navigationLabel = 'News';

    protected static ?string $slug = 'news';

    public static function form(Schema $schema): Schema
    {
        return NewsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NewsTable::configure($table);
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
            'index' => ListNews::route('/'),
            'create' => CreateNews::route('/create'),
            'edit' => EditNews::route('/{record}/edit'),
        ];
    }
}
