<?php

namespace App\Filament\Admin\Resources\Members\Tables;

use App\Models\Member;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('memberNameLast')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->select('members.*')
                ->selectSub(
                    DB::table('account')
                        ->whereColumn('account.memberID', 'members.memberID')
                        ->where('accountVisible', 1)
                        ->selectRaw('COALESCE(SUM("accountValue"), 0)'),
                    'balance'
                ))
            ->columns([
                TextColumn::make('memberNameLast')
                    ->label('Name')
                    ->formatStateUsing(fn ($record): string => trim($record->memberNameFirst . ' ' . $record->memberNameLast))
                    ->searchable(['memberNameFirst', 'memberNameLast'])
                    ->sortable(['memberNameLast', 'memberNameFirst']),
                TextColumn::make('memberCode')
                    ->label('Code')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('memberEmail')
                    ->label('Email')
                    ->placeholder('—')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('memberPhoneMobile')
                    ->label('Mobile')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('AUD')
                    ->color(fn ($state): string => $state < 0 ? 'danger' : ($state > 0 ? 'success' : 'gray'))
                    ->sortable(),
                TextColumn::make('memberActive')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn ($state): string => $state ? 'success' : 'gray'),
                TextColumn::make('memberClaimed')
                    ->label('Claimed')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Yes' : 'No')
                    ->color(fn ($state): string => $state ? 'success' : 'warning')
                    ->icon(fn ($state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-clock')
                    ->tooltip(fn (Member $record): ?string => $record->memberClaimed
                        ? 'Claimed ' . ($record->memberClaimedAt?->format('j M Y'))
                        : null),
            ])
            ->filters([
                SelectFilter::make('memberActive')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
                TernaryFilter::make('memberClaimed')
                    ->label('Claimed'),
            ])
            ->recordActions([
                Action::make('copyClaimLink')
                    ->label('Copy claim link')
                    ->icon('heroicon-o-link')
                    ->color('gray')
                    ->visible(fn (Member $record): bool => ! $record->memberClaimed)
                    // Client-side clipboard copy — no server round-trip needed.
                    ->action(function (): void {})
                    ->extraAttributes(fn (Member $record): array => [
                        'x-on:click' => "navigator.clipboard && navigator.clipboard.writeText('" . url('/claim/' . $record->memberCode) . "')",
                    ]),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Members are never hard-deleted — deactivation is the equivalent.
                    BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['memberActive' => 0]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
