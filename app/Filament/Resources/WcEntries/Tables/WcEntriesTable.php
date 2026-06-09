<?php

namespace App\Filament\Resources\WcEntries\Tables;

use App\Support\MemberDirectory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class WcEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('entry_name')
            ->columns([
                TextColumn::make('entry_name')
                    ->label('Entry')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('memberID')
                    ->label('Member')
                    ->getStateUsing(fn ($record): ?string => MemberDirectory::label((int) $record->memberID))
                    ->placeholder('—'),
                TextColumn::make('orderID')
                    ->label('Order')
                    ->placeholder('—'),
                IconColumn::make('draw_completed')
                    ->label('Draw')
                    ->boolean(),
                TextColumn::make('points')
                    ->label('Live points')
                    ->getStateUsing(fn ($record): int => $record->calculatePoints())
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
            ])
            ->filters([
                TernaryFilter::make('draw_completed')
                    ->label('Draw completed'),
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
