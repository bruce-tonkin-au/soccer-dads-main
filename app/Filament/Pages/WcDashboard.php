<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\WcOverviewStats;
use App\Models\WcSetting;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class WcDashboard extends Page
{
    protected string $view = 'filament.pages.wc-dashboard';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected static string|UnitEnum|null $navigationGroup = 'World Cup 2026';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'World Cup 2026 Dashboard';

    /** Scoring keys managed on this page. */
    protected const SCORING_KEYS = ['points_team_win', 'points_team_draw', 'points_player_goal'];

    public ?array $data = [];

    public function mount(): void
    {
        $values = WcSetting::whereIn('key', self::SCORING_KEYS)->pluck('value', 'key');

        $this->form->fill([
            'points_team_win' => $values['points_team_win'] ?? 3,
            'points_team_draw' => $values['points_team_draw'] ?? 1,
            'points_player_goal' => $values['points_player_goal'] ?? 2,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Scoring configuration')
                    ->description('These values drive the live points calculation across the sweepstake.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('points_team_win')
                            ->label('Points: team win')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        TextInput::make('points_team_draw')
                            ->label('Points: team draw')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        TextInput::make('points_player_goal')
                            ->label('Points: player goal')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach (self::SCORING_KEYS as $key) {
            WcSetting::where('key', $key)->update(['value' => (string) $data[$key]]);
        }

        Notification::make()
            ->title('Scoring configuration saved')
            ->success()
            ->send();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            WcOverviewStats::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 4;
    }
}
