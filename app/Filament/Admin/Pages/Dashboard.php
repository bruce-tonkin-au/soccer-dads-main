<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\NextGamePanel;
use App\Filament\Admin\Widgets\StatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 0;

    // Explicit widget list (order): stats row, then the next-game panel.
    // These only render on this admin-panel dashboard — wc-admin has its own.
    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            NextGamePanel::class,
        ];
    }

    // Single column so both full-width widgets stack in order.
    public function getColumns(): int|array
    {
        return 1;
    }
}
