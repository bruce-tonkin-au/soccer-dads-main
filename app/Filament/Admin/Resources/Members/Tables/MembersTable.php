<?php

namespace App\Filament\Admin\Resources\Members\Tables;

use App\Models\Member;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
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
                TextColumn::make('memberCountry')
                    ->label('Country')
                    ->formatStateUsing(fn (?string $state): string => self::flagEmoji($state))
                    ->tooltip(fn (?string $state): ?string => filled($state) ? strtoupper($state) : null),
                TextColumn::make('memberCode')
                    ->label('Code')
                    ->badge()
                    ->color('gray')
                    // Links to the public registration page. Leaves the admin, so
                    // open in a new tab — Filament emits target="_blank", which
                    // modern browsers treat as implicit rel="noopener".
                    ->url(fn (Member $record): ?string => filled($record->memberCode)
                        ? url('/reg/' . $record->memberCode)
                        : null)
                    ->openUrlInNewTab()
                    ->searchable(),
                // Envelope mailto link, only rendered when the member has an email.
                IconColumn::make('memberEmail')
                    ->label('Email')
                    ->icon(fn (?string $state): ?string => filled($state) ? 'heroicon-o-envelope' : null)
                    ->color('gray')
                    ->url(fn (Member $record): ?string => filled($record->memberEmail) ? 'mailto:' . $record->memberEmail : null)
                    ->tooltip(fn (Member $record): ?string => $record->memberEmail ?: null)
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
                // Inline flip of the active flag straight from the list.
                ToggleColumn::make('memberActive')
                    ->label('Status'),
                TextColumn::make('memberClaimed')
                    ->label('Claimed')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Yes' : 'No')
                    ->color(fn ($state): string => $state ? 'success' : 'warning')
                    ->icon(fn ($state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-clock')
                    ->tooltip(fn (Member $record): ?string => $record->memberClaimed
                        ? 'Claimed ' . ($record->memberClaimedAt?->format('j M Y'))
                        : null),
                // Legacy timestamp columns (members has no created_at/updated_at):
                // memberCreated is set on insert, memberEdited is bumped by a DB
                // trigger on update. Shown in the club's display timezone.
                TextColumn::make('memberCreated')
                    ->label('Created')
                    ->dateTime('d M Y', 'Australia/Adelaide')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('memberEdited')
                    ->label('Updated')
                    ->dateTime('d M Y', 'Australia/Adelaide')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('memberActive')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
                SelectFilter::make('memberClaimed')
                    ->label('Claimed')
                    ->options([
                        1 => 'Claimed',
                        0 => 'Unclaimed',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        '1'     => $query->where('memberClaimed', true),
                        '0'     => $query->where('memberClaimed', false),
                        default => $query,
                    }),
            ])
            ->recordActions([
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

    /**
     * A 2-letter ISO country code → emoji flag, built from the regional-indicator
     * symbols (A → U+1F1E6 … Z → U+1F1FF). Returns "—" for an empty or invalid
     * code. No external library — computed from the code.
     */
    protected static function flagEmoji(?string $code): string
    {
        $code = strtoupper(trim((string) $code));

        if (strlen($code) !== 2 || ! ctype_alpha($code)) {
            return '—';
        }

        $offset = 0x1F1E6 - ord('A');

        return mb_chr(ord($code[0]) + $offset) . mb_chr(ord($code[1]) + $offset);
    }
}
