<?php

namespace App\Filament\Admin\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('member')->withCount('items'))
            ->columns([
                TextColumn::make('orderID')
                    ->label('#')
                    ->prefix('#')
                    ->sortable(),
                // Member full name, else guest orderName, else "Guest" — mirrors
                // the legacy list (leftJoin members / orderName / Guest fallback).
                TextColumn::make('customer')
                    ->label('Customer')
                    ->state(fn (Order $record): string => self::customerName($record))
                    ->description(fn (Order $record): ?string => (! self::hasMemberName($record) && $record->orderName) ? 'Guest' : null),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('orderTotal')
                    ->label('Total')
                    ->money('AUD')
                    ->sortable(),
                // Payment is derived from status (pending → Pending, else Paid) —
                // there is no payment column.
                TextColumn::make('payment')
                    ->label('Payment')
                    ->badge()
                    ->state(fn (Order $record): string => $record->orderStatus === 'pending' ? 'Pending' : 'Paid')
                    ->color(fn (Order $record): string => $record->orderStatus === 'pending' ? 'warning' : 'success'),
                TextColumn::make('orderStatus')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'pending'  => 'warning',
                        'paid'     => 'success',
                        'shipped'  => 'info',
                        'complete' => 'success',
                        'refunded' => 'gray',
                        default    => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('orderStatus')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Pending',
                        'paid'     => 'Paid',
                        'shipped'  => 'Shipped',
                        'complete' => 'Complete',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Manage'),
            ]);
    }

    private static function hasMemberName(Order $order): bool
    {
        return $order->member
            && trim(($order->member->memberNameFirst ?? '') . ' ' . ($order->member->memberNameLast ?? '')) !== '';
    }

    private static function customerName(Order $order): string
    {
        if (self::hasMemberName($order)) {
            return trim($order->member->memberNameFirst . ' ' . $order->member->memberNameLast);
        }

        return $order->orderName ?: 'Guest';
    }
}
