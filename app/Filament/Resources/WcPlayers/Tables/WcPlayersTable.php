<?php

namespace App\Filament\Resources\WcPlayers\Tables;

use App\Models\WcPlayer;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class WcPlayersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('team.flag')
                    ->label(''),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('team.name')
                    ->label('Team')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('shirt_number')
                    ->label('#')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->filters([
                SelectFilter::make('teamID')
                    ->label('Team')
                    ->relationship('team', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('position')
                    ->options(fn () => WcPlayer::query()
                        ->whereNotNull('position')
                        ->distinct()
                        ->orderBy('position')
                        ->pluck('position', 'position')
                        ->all()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
