<?php

namespace App\Filament\Admin\Resources\Orders\Pages;

use App\Filament\Admin\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // "Already refunded" indicator — replaces the refund action once the
            // order is refunded (mirrors the legacy danger-zone note).
            Action::make('alreadyRefunded')
                ->label('Already refunded')
                ->icon('heroicon-o-check-circle')
                ->color('gray')
                ->disabled()
                ->visible(fn (Order $record): bool => $record->orderStatus === 'refunded'),

            // ── Cancel & Refund — LIVE Stripe money movement ────────────────
            // Visible ONLY when status is NOT 'refunded' and NOT 'pending'
            // (exact legacy condition). Delegates entirely to the shared
            // Order::processRefund() — no Stripe/stock logic is duplicated here.
            Action::make('refund')
                ->label('Cancel & Refund')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->visible(fn (Order $record): bool => $record->orderStatus !== 'refunded' && $record->orderStatus !== 'pending')
                ->requiresConfirmation()
                ->modalHeading(fn (Order $record): string => 'Refund order #' . $record->orderID . '?')
                ->modalDescription(fn (Order $record): string => 'Full refund: $' . number_format($record->orderTotal, 2)
                    . '. Order status → Refunded. Stock levels will be restored. This will charge back via Stripe and cannot be undone.')
                ->modalSubmitActionLabel('Refund via Stripe')
                ->action(function (Order $record): void {
                    $result = $record->processRefund();

                    $notification = Notification::make()
                        ->title($result['success'] ? 'Refund complete' : 'Refund failed')
                        ->body($result['message']);

                    $result['success'] ? $notification->success() : $notification->danger();
                    $notification->send();

                    if ($result['success']) {
                        // Reload so status, badges and the danger-zone actions
                        // reflect the now-refunded order.
                        $this->redirect(OrderResource::getUrl('edit', ['record' => $record]));
                    }
                }),

            // ── Delete order — always visible ───────────────────────────────
            // Deletes order_items then the order (mirror deleteOrder). No Stripe,
            // no stock restore.
            Action::make('delete')
                ->label('Delete order')
                ->icon('heroicon-o-trash')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading(fn (Order $record): string => 'Delete order #' . $record->orderID . '?')
                ->modalDescription(fn (Order $record): string => 'Delete order #' . $record->orderID
                    . ' and all its line items? Stock is not restored. This cannot be undone.')
                ->modalSubmitActionLabel('Delete order')
                ->action(function (Order $record) {
                    DB::table('order_items')->where('orderID', $record->orderID)->delete();
                    DB::table('orders')->where('orderID', $record->orderID)->delete();

                    Notification::make()
                        ->title('Order #' . $record->orderID . ' deleted.')
                        ->success()
                        ->send();

                    return $this->redirect(OrderResource::getUrl('index'));
                }),
        ];
    }
}
