<?php

namespace App\Filament\Widgets;

use App\Models\WcEntry;
use App\Models\WcFixture;
use App\Models\WcGoal;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WcOverviewStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalEntries = WcEntry::count();
        $drawCompleted = WcEntry::where('draw_completed', true)->count();
        $fixturesTotal = WcFixture::count();
        $fixturesDone = WcFixture::where('status', 'completed')->count();
        $totalGoals = WcGoal::count();

        return [
            Stat::make('Entries', $totalEntries)
                ->description('Total sweepstake entries')
                ->icon('heroicon-o-ticket')
                ->color('primary'),
            Stat::make('Draws completed', $drawCompleted)
                ->description($totalEntries > 0 ? "{$drawCompleted} of {$totalEntries} entries" : 'No entries yet')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make('Fixtures played', "{$fixturesDone} / {$fixturesTotal}")
                ->description('Completed of total')
                ->icon('heroicon-o-calendar')
                ->color('info'),
            Stat::make('Goals recorded', $totalGoals)
                ->description('Across all fixtures')
                ->icon('heroicon-o-trophy')
                ->color('warning'),
        ];
    }
}
