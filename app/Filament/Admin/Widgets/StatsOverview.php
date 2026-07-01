<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends StatsOverviewWidget
{
    // Mirrors AdminController::dashboard()'s $stats exactly.
    protected function getStats(): array
    {
        $players = DB::table('members')->count();
        $seasons = DB::table('seasons')->where('seasonVisible', 1)->count();
        $games   = DB::table('games')->where('gameVisible', 1)->count();
        $goals   = DB::table('scoring-actions')
            ->where('actionGoal', 1)
            ->where('actionActive', 1)
            ->whereNotIn('gameID', fn ($q) => $q->select('gameID')->from('games')->where('is_test', true))
            ->count();

        return [
            Stat::make('Players', $players)
                ->icon('heroicon-o-users')
                ->color('info'),
            Stat::make('Seasons', $seasons)
                ->icon('heroicon-o-calendar')
                ->color('success'),
            Stat::make('Game nights', $games)
                ->icon('heroicon-o-calendar-days')
                ->color('warning'),
            Stat::make('Goals scored', $goals)
                ->icon('heroicon-o-trophy')
                ->color('primary'),
        ];
    }
}
