<?php

namespace App\Filament\Admin\Pages;

use App\Services\RegistrationActions;
use App\Services\RegistrationsQuery;
use BackedEnum;
use UnitEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class Registrations extends Page
{
    protected string $view = 'filament.admin.pages.registrations';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|UnitEnum|null $navigationGroup = 'Play';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Registrations';

    protected static ?string $slug = 'registrations';

    protected static ?string $title = 'Registrations';

    // Selected game — bound to the dropdown (wire:model.live) and shareable via
    // the URL. Null resolves to the next game (or latest) in the query.
    #[Url]
    public ?int $gameID = null;

    public function mount(): void
    {
        // Resolve the default game so the selector shows the right option.
        $data = app(RegistrationsQuery::class)->data($this->gameID);
        $this->gameID = $data['game']->gameID ?? null;
    }

    /**
     * Re-run on every render (including after each Livewire action), so the
     * grouped lists, capacity and event log always reflect current state.
     * Mirrors AdminController::registrations via the shared query, then derives
     * the same active/bench/not-going groupings + capacity the legacy screen shows.
     */
    protected function getViewData(): array
    {
        $data = app(RegistrationsQuery::class)->data($this->gameID);

        $registrations = $data['registrations'];

        $active   = $registrations->where('registrationStatus', 1)->where('registrationBench', 0)->values();
        $bench     = $registrations->where('registrationStatus', 1)->where('registrationBench', 1)->values();
        $notGoing = $registrations->where('registrationStatus', 2)->values();

        $activeCount = $active->count();

        return array_merge($data, [
            'active'      => $active,
            'bench'       => $bench,
            'notGoing'    => $notGoing,
            'activeCount' => $activeCount,
            'benchCount'  => $bench->count(),
            'capFill'     => $activeCount > 0 ? min(100, (int) round($activeCount / 18 * 100)) : 0,
            'capColor'    => $activeCount > 18 ? 'danger' : ($activeCount >= 16 ? 'warning' : 'success'),
            'overCap'     => max(0, $activeCount - 18),
        ]);
    }

    // ── Livewire actions — all delegate to the shared RegistrationActions ──

    public function register(int $memberID): void
    {
        if (! $this->gameID) {
            return;
        }
        app(RegistrationActions::class)->register($this->gameID, $memberID);
        Notification::make()->title('Player registered.')->success()->send();
    }

    public function deregister(int $memberID): void
    {
        if (! $this->gameID) {
            return;
        }
        app(RegistrationActions::class)->deregister($this->gameID, $memberID);
        Notification::make()->title('Player marked as not going.')->success()->send();
    }

    public function promote(int $memberID): void
    {
        if (! $this->gameID) {
            return;
        }
        app(RegistrationActions::class)->promote($this->gameID, $memberID);
        Notification::make()->title('Player promoted to active.')->success()->send();
    }

    public function moveBench(int $memberID, string $direction): void
    {
        if (! $this->gameID || ! in_array($direction, ['up', 'down'], true)) {
            return;
        }
        app(RegistrationActions::class)->moveBench($this->gameID, $memberID, $direction);
    }

    public function toggleBench(int $memberID): void
    {
        if (! $this->gameID) {
            return;
        }
        app(RegistrationActions::class)->toggleBench($this->gameID, $memberID);
    }
}
