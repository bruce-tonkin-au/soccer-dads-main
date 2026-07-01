<?php

namespace App\Filament\Admin\Resources\Finances\Pages;

use App\Filament\Admin\Resources\Finances\FinanceResource;
use App\Filament\Admin\Resources\Finances\Widgets\FinancesOverview;
use App\Models\Account;
use App\Models\Member;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListFinances extends ListRecords
{
    protected static string $resource = FinanceResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            FinancesOverview::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->newTransactionAction(),
            $this->transferAction(),
        ];
    }

    /** Active members, "Last First" order — mirrors AdminController::financeMembers. */
    private function memberOptions(): array
    {
        return Member::query()
            ->where('memberActive', 1)
            ->orderBy('memberNameLast')
            ->orderBy('memberNameFirst')
            ->get()
            ->mapWithKeys(fn (Member $m): array => [
                $m->memberID => trim($m->memberNameFirst . ' ' . $m->memberNameLast),
            ])
            ->all();
    }

    // Mirrors AdminController::storeTransaction — inserts ONE account row.
    private function newTransactionAction(): Action
    {
        return Action::make('newTransaction')
            ->label('New Transaction')
            ->icon('heroicon-o-plus')
            ->modalHeading('New transaction')
            ->modalSubmitActionLabel('Save transaction')
            ->schema([
                Select::make('memberID')
                    ->label('Member')
                    ->options(fn (): array => $this->memberOptions())
                    ->searchable()
                    ->required()
                    ->exists('members', 'memberID'),
                Select::make('type')
                    ->label('Type')
                    ->options([
                        'deposit' => 'Deposit (credit)',
                        'charge'  => 'Charge (debit)',
                    ])
                    ->default('deposit')
                    ->required(),
                TextInput::make('amount')
                    ->label('Amount')
                    ->prefix('$')
                    ->numeric()
                    ->minValue(0.01)
                    ->maxValue(10000)
                    ->required(),
                TextInput::make('description')
                    ->label('Description')
                    ->maxLength(255)
                    ->required(),
                DatePicker::make('date')
                    ->label('Date')
                    ->default(now())
                    ->required(),
            ])
            ->action(function (array $data): void {
                $amount = round((float) $data['amount'], 2);

                Account::create([
                    'memberID'       => $data['memberID'],
                    'accountValue'   => $data['type'] === 'charge' ? -$amount : $amount,
                    'gameID'         => null,
                    'accountComment' => $data['description'],
                    'accountVisible' => 1,
                    'accountCreated' => Carbon::parse($data['date']),
                    'accountEdited'  => now(),
                ]);

                Notification::make()->title('Transaction added.')->success()->send();
                $this->dispatch('finances-updated');
            });
    }

    // Mirrors AdminController::storeTransfer — TWO rows in one DB::transaction.
    private function transferAction(): Action
    {
        return Action::make('transfer')
            ->label('Transfer')
            ->icon('heroicon-o-arrows-right-left')
            ->color('gray')
            ->modalHeading('Transfer between members')
            ->modalSubmitActionLabel('Complete transfer')
            ->schema([
                Select::make('fromMemberID')
                    ->label('From member (debited)')
                    ->options(fn (): array => $this->memberOptions())
                    ->searchable()
                    ->required()
                    ->different('toMemberID')
                    ->exists('members', 'memberID'),
                Select::make('toMemberID')
                    ->label('To member (credited)')
                    ->options(fn (): array => $this->memberOptions())
                    ->searchable()
                    ->required()
                    ->exists('members', 'memberID'),
                TextInput::make('amount')
                    ->label('Amount')
                    ->prefix('$')
                    ->numeric()
                    ->minValue(0.01)
                    ->maxValue(10000)
                    ->required(),
                TextInput::make('description')
                    ->label('Description')
                    ->maxLength(255)
                    ->required(),
                DatePicker::make('date')
                    ->label('Date')
                    ->default(now())
                    ->required(),
            ])
            ->action(function (array $data): void {
                $amount  = round((float) $data['amount'], 2);
                $comment = $data['description'] . ' (transfer)';
                $created = Carbon::parse($data['date']);
                $now     = now();

                // Both legs succeed or neither does.
                DB::transaction(function () use ($data, $amount, $comment, $created, $now): void {
                    Account::create([
                        'memberID'       => $data['fromMemberID'],
                        'accountValue'   => -$amount,
                        'gameID'         => null,
                        'accountComment' => $comment,
                        'accountVisible' => 1,
                        'accountCreated' => $created,
                        'accountEdited'  => $now,
                    ]);

                    Account::create([
                        'memberID'       => $data['toMemberID'],
                        'accountValue'   => $amount,
                        'gameID'         => null,
                        'accountComment' => $comment,
                        'accountVisible' => 1,
                        'accountCreated' => $created,
                        'accountEdited'  => $now,
                    ]);
                });

                Notification::make()->title('Transfer completed.')->success()->send();
                $this->dispatch('finances-updated');
            });
    }
}
