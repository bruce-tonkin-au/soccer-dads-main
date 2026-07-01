<?php

namespace App\Filament\Admin\Resources\Finances\Tables;

use App\Models\Account;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FinancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('accountCreated', 'desc')
            // Visible rows only, joined to their member (inner — mirrors the
            // legacy join(members)), with paymentSource left-joined for the
            // description fallback.
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->where('account.accountVisible', 1)
                ->whereHas('member')
                ->leftJoin('account-payments as ap', 'account.paymentID', '=', 'ap.paymentID')
                ->with('member')
                ->select('account.*', 'ap.paymentSource'))
            ->columns([
                TextColumn::make('player')
                    ->label('Player')
                    ->state(fn (Account $record): string => $record->member
                        ? trim($record->member->memberNameFirst . ' ' . $record->member->memberNameLast)
                        : '—')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereHas('member', fn (Builder $q): Builder => $q
                            ->where('memberNameFirst', 'ilike', "%{$search}%")
                            ->orWhere('memberNameLast', 'ilike', "%{$search}%"))),
                TextColumn::make('accountCreated')
                    ->label('Date')
                    ->date('j M Y')
                    ->sortable(),
                TextColumn::make('accountValue')
                    ->label('Amount')
                    ->money('AUD')
                    ->color(fn ($state): string => (float) $state > 0 ? 'success' : ((float) $state < 0 ? 'danger' : 'gray'))
                    ->sortable(),
                // Deposit / Charge derived purely from the sign (no type column).
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->state(fn (Account $record): string => (float) $record->accountValue > 0 ? 'Deposit' : 'Charge')
                    ->color(fn (Account $record): string => (float) $record->accountValue > 0 ? 'success' : 'gray'),
                TextColumn::make('description')
                    ->label('Description')
                    ->state(fn (Account $record): string => self::describe($record)),
            ]);
        // No recordActions and no toolbar/bulk actions — the ledger is
        // append-only; corrections are made as opposite entries.
    }

    // Mirrors the legacy finances.blade.php description logic exactly, including
    // relabelling Stripe-session-looking strings to "Account top-up".
    private static function describe(Account $record): string
    {
        $isDeposit = (float) $record->accountValue > 0;

        if ($record->accountComment) {
            $description = $record->accountComment;
        } elseif ($isDeposit && $record->paymentSource) {
            $description = ucfirst($record->paymentSource);
        } elseif ($isDeposit) {
            $description = 'Payment';
        } elseif ($record->gameID) {
            $description = 'Game #' . $record->gameID;
        } else {
            $description = '—';
        }

        if (str_starts_with($description, 'Stripe top-up')
            || str_starts_with($description, 'cs_live_')
            || str_starts_with($description, 'cs_test_')) {
            $description = 'Account top-up';
        }

        return $description;
    }
}
