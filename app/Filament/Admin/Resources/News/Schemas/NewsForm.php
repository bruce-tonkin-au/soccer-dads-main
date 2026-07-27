<?php

namespace App\Filament\Admin\Resources\News\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NewsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('newsTitle')
                    ->label('Title')
                    ->required()
                    ->maxLength(255),
                // Same disk + shape as the product images (ImagesRelationManager):
                // Filament stores the disk-relative path ('news/{file}'), which
                // the public views resolve via asset('storage/'.newsImage).
                FileUpload::make('newsImage')
                    ->label('Image')
                    ->image()
                    ->disk('public')
                    ->directory('news')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(5120),
                DatePicker::make('newsDate')
                    ->label('Date')
                    ->required()
                    ->default(now()),
                RichEditor::make('newsBody')
                    ->label('Body')
                    ->required()
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link'],
                    ]),
                Select::make('newsActive')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->default(1)
                    ->selectablePlaceholder(false)
                    ->required(),
            ]);
    }
}
