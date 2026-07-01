<?php

namespace App\Filament\Admin\Resources\Products\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('productName')
                    ->label('Product name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('productDescription')
                    ->label('Description')
                    ->rows(4),
                TextInput::make('productPrice')
                    ->label('Price')
                    ->prefix('$')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->default('0.00'),
                TextInput::make('productStock')
                    ->label('Stock')
                    ->integer()
                    ->minValue(0)
                    ->required()
                    ->default(0),
                // Editable on edit; on create it may be left blank and is
                // auto-generated from the name (see CreateProduct). Uniqueness
                // mirrors the legacy storeProduct / updateProduct slug rules.
                TextInput::make('productSlug')
                    ->label('Slug')
                    ->helperText('Leave blank on create to generate from the name. Lowercase letters, numbers and hyphens only.')
                    ->maxLength(255)
                    ->rule('regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                    ->unique(ignoreRecord: true),
                Toggle::make('productActive')
                    ->label('Active (visible in store)')
                    ->default(true),
                DateTimePicker::make('productAvailableFrom')
                    ->label('Available from')
                    ->seconds(false)
                    ->helperText('Leave blank to show immediately'),
                DateTimePicker::make('productAvailableTo')
                    ->label('Available to')
                    ->seconds(false)
                    ->afterOrEqual('productAvailableFrom')
                    ->helperText('Leave blank for no end date'),
                TextInput::make('productMaxQuantity')
                    ->label('Max quantity per order')
                    ->integer()
                    ->minValue(1)
                    ->required()
                    ->default(1),
            ]);
    }
}
