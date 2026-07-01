<?php

namespace App\Filament\Admin\Resources\Finances;

use App\Filament\Admin\Resources\Finances\Pages\ListFinances;
use App\Filament\Admin\Resources\Finances\Tables\FinancesTable;
use App\Models\Account;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class FinanceResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationLabel = 'Finances';

    protected static ?string $modelLabel = 'transaction';

    protected static ?string $pluralModelLabel = 'transactions';

    protected static ?string $slug = 'finances';

    public static function table(Table $table): Table
    {
        return FinancesTable::configure($table);
    }

    // Read-only, append-only ledger — no form, no create/edit/delete pages.
    // Entries are made via the two custom header actions on ListFinances.
    public static function getPages(): array
    {
        return [
            'index' => ListFinances::route('/'),
        ];
    }
}
