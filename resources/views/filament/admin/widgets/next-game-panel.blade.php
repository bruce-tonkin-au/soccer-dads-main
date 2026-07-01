<div>
    @if (! $nextGame)
        <x-filament::section icon="heroicon-o-calendar">
            <x-slot name="heading">Next game</x-slot>
            <p class="text-sm text-gray-500 dark:text-gray-400">No upcoming game.</p>
        </x-filament::section>
    @else
        <x-filament::section icon="heroicon-o-calendar-days">
            <x-slot name="heading">
                Next game — {{ \Carbon\Carbon::parse($game->gameDate)->format('l j F Y') }}
            </x-slot>
            <x-slot name="description">
                {{ $game->seasonName }} — Round {{ $game->gameRound }} · Game ID {{ $game->gameID }}
                · <span class="font-semibold text-primary-600 dark:text-primary-400">★ Next game</span>
            </x-slot>

            {{-- Header links (team/print/edit not migrated yet → legacy /admin routes) --}}
            <div class="flex flex-wrap gap-2">
                <x-filament::button tag="a" href="/admin/teams/{{ $game->gameID }}" icon="heroicon-m-users" color="primary" size="sm">
                    Manage teams
                </x-filament::button>
                <x-filament::button tag="a" href="/manage/registrations?gameID={{ $game->gameID }}" icon="heroicon-m-clipboard-document-check" color="gray" size="sm">
                    Registrations
                </x-filament::button>
                <x-filament::button tag="a" href="/admin/print/{{ $game->gameID }}" target="_blank" icon="heroicon-m-printer" color="gray" size="sm">
                    Print sheet
                </x-filament::button>
                <x-filament::button tag="a" href="/admin/seasons/{{ $game->gameSeasonID }}/games/{{ $game->gameID }}/edit" icon="heroicon-m-pencil" color="gray" size="sm">
                    Edit game
                </x-filament::button>
            </div>

            {{-- Registered (active) — each with a "mark not going" action --}}
            <div class="mt-6">
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Registered — {{ $active->count() }}/{{ $playersCount }}
                </p>
                @if ($active->isEmpty())
                    <p class="text-sm text-gray-400 dark:text-gray-500">No players registered yet.</p>
                @else
                    <div class="flex flex-wrap gap-2">
                        @foreach ($active as $r)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 py-1 pl-3 pr-1 text-sm font-medium text-gray-800 dark:bg-white/10 dark:text-gray-200">
                                {{ $r->memberNameFirst }} {{ $r->memberNameLast }}
                                <x-filament::icon-button
                                    icon="heroicon-m-arrow-down"
                                    color="gray"
                                    size="sm"
                                    label="Mark not going"
                                    tooltip="Mark not going"
                                    wire:click="deregister({{ $r->memberID }})"
                                />
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Bench — DISPLAY ONLY (no actions, mirrors legacy) --}}
            @if ($bench->isNotEmpty())
                <div class="mt-6">
                    <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Bench</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($bench as $r)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-warning-300 bg-warning-50 px-3 py-1 text-sm font-medium text-warning-700 dark:border-warning-400/30 dark:bg-warning-400/10 dark:text-warning-400">
                                <span class="font-bold">B{{ $r->benchSequence }}</span>
                                {{ $r->memberNameFirst }} {{ $r->memberNameLast }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Recently played, not registered — each with a "register" action --}}
            @if ($recent->isNotEmpty())
                <div class="mt-6">
                    <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Not yet registered — played recently</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($recent as $r)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-dashed border-gray-300 py-1 pl-3 pr-1 text-sm font-medium text-gray-500 dark:border-white/20 dark:text-gray-400">
                                {{ $r->memberNameFirst }} {{ $r->memberNameLast }}
                                <x-filament::icon-button
                                    icon="heroicon-m-arrow-up"
                                    color="success"
                                    size="sm"
                                    label="Register for game"
                                    tooltip="Register for game"
                                    wire:click="register({{ $r->memberID }})"
                                />
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Not going — each with a "register" action --}}
            @if ($notGoing->isNotEmpty())
                <div class="mt-6">
                    <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Not going</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($notGoing as $r)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-danger-200 bg-danger-50 py-1 pl-3 pr-1 text-sm font-medium text-danger-700 dark:border-danger-400/20 dark:bg-danger-400/10 dark:text-danger-400">
                                {{ $r->memberNameFirst }} {{ $r->memberNameLast }}
                                <x-filament::icon-button
                                    icon="heroicon-m-arrow-up"
                                    color="success"
                                    size="sm"
                                    label="Register for game"
                                    tooltip="Register for game"
                                    wire:click="register({{ $r->memberID }})"
                                />
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- reset night --}}
            <div class="mt-6 text-right">
                <button
                    type="button"
                    class="text-xs text-gray-400 underline hover:text-danger-600 dark:text-gray-500"
                    wire:click="resetNight"
                    wire:confirm="Reset the start time for this game night?"
                >
                    reset night
                </button>
            </div>
        </x-filament::section>
    @endif
</div>
