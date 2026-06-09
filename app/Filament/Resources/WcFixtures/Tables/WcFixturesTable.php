<?php

namespace App\Filament\Resources\WcFixtures\Tables;

use App\Filament\Resources\WcFixtures\Schemas\WcFixtureForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WcFixturesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('match_datetime')
            ->columns([
                TextColumn::make('match_datetime')
                    ->label('Kick-off')
                    ->dateTime('D j M, H:i')
                    ->sortable(),
                TextColumn::make('group_letter')
                    ->label('Grp')
                    ->badge(),
                TextColumn::make('homeTeam.name')
                    ->label('Home')
                    ->formatStateUsing(fn ($state, $record) => trim(($record->homeTeam?->flag ?? '') . ' ' . ($state ?? $record->home_placeholder)))
                    ->searchable(),
                TextColumn::make('score')
                    ->label('Score')
                    ->getStateUsing(fn ($record) => is_null($record->home_score) && is_null($record->away_score)
                        ? '–'
                        : ($record->home_score ?? '?') . ' : ' . ($record->away_score ?? '?'))
                    ->badge()
                    ->alignCenter(),
                TextColumn::make('awayTeam.name')
                    ->label('Away')
                    ->formatStateUsing(fn ($state, $record) => trim(($record->awayTeam?->flag ?? '') . ' ' . ($state ?? $record->away_placeholder)))
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => WcFixtureForm::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'completed' => 'success',
                        'live' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('stage')
                    ->options([
                        'group' => 'Group',
                        'round_of_32' => 'Round of 32',
                        'round_of_16' => 'Round of 16',
                        'quarter_final' => 'Quarter-final',
                        'semi_final' => 'Semi-final',
                        'final' => 'Final',
                    ]),
                SelectFilter::make('status')
                    ->options(WcFixtureForm::STATUSES),
                SelectFilter::make('group_letter')
                    ->label('Group')
                    ->options(array_combine(range('A', 'L'), range('A', 'L'))),
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
