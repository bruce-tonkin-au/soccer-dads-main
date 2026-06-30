<?php

namespace App\Filament\Admin\Resources\Commentators\Tables;

use App\Models\Commentator;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommentatorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('commentatorNameFirst')
            ->columns([
                TextColumn::make('commentatorNameFirst')
                    ->label('Name')
                    ->formatStateUsing(fn (Commentator $record): string => trim($record->commentatorNameFirst . ' ' . $record->commentatorNameLast))
                    ->searchable(['commentatorNameFirst', 'commentatorNameLast'])
                    ->sortable(),
                TextColumn::make('commentatorAge')
                    ->label('Age')
                    ->placeholder('—'),
                TextColumn::make('commentatorElevenLabsID')
                    ->label('ElevenLabs Voice ID')
                    ->badge()
                    ->color('gray')
                    ->limit(20)
                    ->placeholder('—'),
                TextColumn::make('commentatorDefault')
                    ->label('Default')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Default' : '—')
                    ->color(fn ($state): string => $state ? 'warning' : 'gray'),
                TextColumn::make('commentatorActive')
                    ->label('Active')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Yes' : 'No')
                    ->color(fn ($state): string => $state ? 'success' : 'gray'),
                TextColumn::make('commentatorVisible')
                    ->label('Visible')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Yes' : 'No')
                    ->color(fn ($state): string => $state ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('commentatorActive')
                    ->label('Active')
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
