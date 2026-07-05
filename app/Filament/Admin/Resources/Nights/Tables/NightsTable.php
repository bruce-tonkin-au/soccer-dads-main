<?php

namespace App\Filament\Admin\Resources\Nights\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NightsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nightSort', 'asc')
            ->columns([
                TextColumn::make('nightName')
                    ->label('Night')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nightVenue')
                    ->label('Venue')
                    ->searchable(),
                TextColumn::make('nightActive')
                    ->label('Active')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn ($state): string => $state ? 'success' : 'gray'),
                TextColumn::make('nightSort')
                    ->label('Sort')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
