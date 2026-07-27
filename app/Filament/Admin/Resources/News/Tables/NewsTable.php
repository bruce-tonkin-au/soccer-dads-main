<?php

namespace App\Filament\Admin\Resources\News\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('newsDate', 'desc')
            ->columns([
                ImageColumn::make('newsImage')
                    ->label('Image')
                    ->disk('public')
                    ->square()
                    ->size(48),
                TextColumn::make('newsTitle')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('newsDate')
                    ->label('Date')
                    ->date('j M Y')
                    ->sortable(),
                TextColumn::make('newsActive')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn ($state): string => $state ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('newsActive')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
