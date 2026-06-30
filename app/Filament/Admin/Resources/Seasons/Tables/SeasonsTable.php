<?php

namespace App\Filament\Admin\Resources\Seasons\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SeasonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('seasonID', 'desc')
            ->columns([
                TextColumn::make('seasonName')
                    ->label('Season')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('seasonLink')
                    ->label('Link')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('seasonVisible')
                    ->label('Visible')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Visible' : 'Hidden')
                    ->color(fn ($state): string => $state ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('seasonVisible')
                    ->label('Visible')
                    ->options([
                        1 => 'Visible',
                        0 => 'Hidden',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
