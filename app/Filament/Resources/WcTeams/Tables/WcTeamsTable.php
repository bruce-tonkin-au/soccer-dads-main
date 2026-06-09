<?php

namespace App\Filament\Resources\WcTeams\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WcTeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('group_letter')
            ->columns([
                TextColumn::make('flag')
                    ->label(''),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->searchable()
                    ->badge(),
                TextColumn::make('confederation')
                    ->sortable(),
                TextColumn::make('group_letter')
                    ->label('Group')
                    ->formatStateUsing(fn ($state, $record) => trim($state . ($record->group_position ? $record->group_position : '')))
                    ->sortable(),
                TextColumn::make('pot')
                    ->sortable(),
                TextColumn::make('seed_tier')
                    ->label('Tier')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 1 ? 'Top 24' : 'Bottom 24')
                    ->color(fn ($state) => $state === 1 ? 'success' : 'gray'),
                TextColumn::make('fifa_ranking')
                    ->label('FIFA')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('qualified')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('seed_tier')
                    ->label('Seed tier')
                    ->options([1 => 'Top 24', 2 => 'Bottom 24']),
                SelectFilter::make('confederation')
                    ->options(fn () => \App\Models\WcTeam::query()
                        ->distinct()
                        ->orderBy('confederation')
                        ->pluck('confederation', 'confederation')
                        ->all()),
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
