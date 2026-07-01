<?php

namespace App\Filament\Admin\Resources\Products\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('productName')
            ->columns([
                // productImage holds an absolute URL (asset('storage/…') of the
                // primary image, or a manually-entered URL) — so no disk is set;
                // the column renders the stored URL directly.
                ImageColumn::make('productImage')
                    ->label('')
                    ->square()
                    ->size(44),
                TextColumn::make('productName')
                    ->label('Product')
                    ->description(fn ($record): string => '/store/' . $record->productSlug)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('productPrice')
                    ->label('Price')
                    ->money('AUD')
                    ->sortable(),
                TextColumn::make('productStock')
                    ->label('Stock')
                    ->formatStateUsing(fn ($state): string => (int) $state <= 0 ? 'Out of stock' : (string) $state)
                    ->color(fn ($state): ?string => (int) $state <= 0 ? 'danger' : null)
                    ->sortable(),
                // Inline activate/deactivate — mirrors AdminStoreController::toggleProduct.
                ToggleColumn::make('productActive')
                    ->label('Active'),
            ])
            ->filters([
                SelectFilter::make('productActive')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
