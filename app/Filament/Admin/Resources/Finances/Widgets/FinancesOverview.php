<?php

namespace App\Filament\Admin\Resources\Finances\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class FinancesOverview extends StatsOverviewWidget
{
    // Re-render when a transaction/transfer is added on ListFinances.
    #[On('finances-updated')]
    public function refreshStats(): void
    {
        // No-op: receiving the event re-renders the widget with fresh getStats().
    }

    protected function getStats(): array
    {
        // Per-member balance = SUM(accountValue) WHERE accountVisible=1, then
        // totalOwing = sum of negative balances, totalOwed = sum of positive —
        // identical to AdminController::dashboard.
        $balances = DB::table('account')
            ->where('accountVisible', 1)
            ->select('memberID', DB::raw('SUM("accountValue") as balance'))
            ->groupBy('memberID')
            ->get();

        $totalOwing = (float) $balances->where('balance', '<', 0)->sum('balance'); // negative
        $totalOwed  = (float) $balances->where('balance', '>', 0)->sum('balance');

        return [
            Stat::make('Total owing', '$' . number_format(abs($totalOwing), 2))
                ->description('Owed to the club by members')
                ->icon('heroicon-o-arrow-trending-down')
                ->color('danger'),
            Stat::make('Total in credit', '$' . number_format($totalOwed, 2))
                ->description('Held as member credit')
                ->icon('heroicon-o-arrow-trending-up')
                ->color('success'),
        ];
    }
}
