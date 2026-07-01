<?php

namespace App\Filament\Admin\Resources\Orders\Schemas;

use App\Filament\Admin\Resources\Members\MemberResource;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── Line items (read-only) ──────────────────────────────────
                Section::make('Line items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('product.productName')
                                    ->label('Product'),
                                TextEntry::make('itemQuantity')
                                    ->label('Qty'),
                                TextEntry::make('itemPrice')
                                    ->label('Unit price')
                                    ->money('AUD'),
                                TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('AUD')
                                    ->state(fn (OrderItem $record): float => (float) $record->itemPrice * (int) $record->itemQuantity),
                            ])
                            ->columns(4),
                        TextEntry::make('orderTotal')
                            ->label('Order total')
                            ->money('AUD'),
                    ]),

                // ── Customer (read-only) ────────────────────────────────────
                Section::make('Customer')
                    ->schema([
                        TextEntry::make('customer_name')
                            ->label('Name')
                            ->state(fn (Order $record): string => self::hasMemberName($record)
                                ? trim($record->member->memberNameFirst . ' ' . $record->member->memberNameLast)
                                : ($record->orderName ?: '—')),
                        TextEntry::make('memberID')
                            ->label('Member ID')
                            ->formatStateUsing(fn ($state): string => '#' . $state)
                            ->url(fn (Order $record): ?string => self::hasMemberName($record)
                                ? MemberResource::getUrl('edit', ['record' => $record->memberID])
                                : null)
                            ->visible(fn (Order $record): bool => self::hasMemberName($record)),
                        TextEntry::make('orderEmail')
                            ->label('Email')
                            ->placeholder('—')
                            ->visible(fn (Order $record): bool => ! self::hasMemberName($record) && (bool) $record->orderEmail),
                        TextEntry::make('orderPhone')
                            ->label('Phone')
                            ->visible(fn (Order $record): bool => ! self::hasMemberName($record) && (bool) $record->orderPhone),
                        TextEntry::make('guest_note')
                            ->hiddenLabel()
                            ->state(fn (Order $record): string => ($record->orderName || $record->orderEmail)
                                ? 'Guest order — not linked to a member account.'
                                : 'Guest order — no customer details recorded.')
                            ->color('gray')
                            ->visible(fn (Order $record): bool => ! self::hasMemberName($record)),
                    ]),

                // ── Payment (read-only) ─────────────────────────────────────
                Section::make('Payment')
                    ->schema([
                        TextEntry::make('payment_status')
                            ->label('Payment status')
                            ->badge()
                            ->state(fn (Order $record): string => $record->orderStatus === 'pending' ? 'Pending' : 'Paid')
                            ->color(fn (Order $record): string => $record->orderStatus === 'pending' ? 'warning' : 'success'),
                        TextEntry::make('stripeSessionID')
                            ->label('Stripe session')
                            ->visible(fn (Order $record): bool => (bool) $record->stripeSessionID),
                        TextEntry::make('created_at')
                            ->label('Order date')
                            ->dateTime('d M Y, g:ia'),
                    ]),

                // ── Update order (editable — status + notes only) ───────────
                // Mirrors updateOrder: saving writes ONLY orderStatus and
                // orderNotes. Setting 'refunded' here is pure record-keeping —
                // it does NOT trigger Stripe or stock (that is the refund action).
                Section::make('Update order')
                    ->schema([
                        Select::make('orderStatus')
                            ->label('Status')
                            ->options([
                                'pending'  => 'Pending',
                                'paid'     => 'Paid',
                                'shipped'  => 'Shipped',
                                'complete' => 'Complete',
                                'refunded' => 'Refunded',
                            ])
                            ->required(),
                        Textarea::make('orderNotes')
                            ->label('Notes')
                            ->rows(3),
                    ]),
            ]);
    }

    private static function hasMemberName(Order $order): bool
    {
        return (bool) $order->memberID
            && $order->member
            && trim(($order->member->memberNameFirst ?? '') . ' ' . ($order->member->memberNameLast ?? '')) !== '';
    }
}
