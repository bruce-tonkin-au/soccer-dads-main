<x-filament-panels::page>
    {{-- Game selector --}}
    <div class="flex flex-col gap-1.5 sm:max-w-md">
        <label for="registrations-game" class="text-sm font-medium text-gray-950 dark:text-white">Game</label>
        <x-filament::input.wrapper>
            <x-filament::input.select id="registrations-game" wire:model.live="gameID">
                @foreach ($allGames as $g)
                    <option value="{{ $g->gameID }}">
                        {{ $g->seasonName }} — Round {{ $g->gameRound }}
                        ({{ \Carbon\Carbon::parse($g->gameDate)->format('d M Y') }})@if ($nextGame && $g->gameID == $nextGame->gameID) ★ Next game @endif
                    </option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>

    @if (! $game)
        <x-filament::section icon="heroicon-o-calendar">
            <p class="text-sm text-gray-500 dark:text-gray-400">No games found.</p>
        </x-filament::section>
    @else
        @php
            $capMap = [
                'success' => ['text' => 'text-success-600 dark:text-success-400', 'bar' => 'bg-success-500'],
                'warning' => ['text' => 'text-warning-600 dark:text-warning-400', 'bar' => 'bg-warning-500'],
                'danger'  => ['text' => 'text-danger-600 dark:text-danger-400',  'bar' => 'bg-danger-500'],
            ];
            $capText = $capMap[$capColor]['text'];
            $capBar  = $capMap[$capColor]['bar'];
        @endphp

        {{-- Game header + capacity --}}
        <x-filament::section icon="heroicon-o-calendar-days">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            {{ $game->seasonName }} — Round {{ $game->gameRound }}
                        </h3>
                        @if ($isNextGame)
                            <x-filament::badge color="primary" size="sm">★ Next game</x-filament::badge>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
                        <span>{{ \Carbon\Carbon::parse($game->gameDate)->format('l j F Y') }}</span>
                        <span aria-hidden="true">&middot;</span>
                        <span>Game ID {{ $game->gameID }}</span>
                    </div>
                </div>

                <div class="w-full lg:max-w-xs">
                    <div class="flex items-baseline justify-between">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Active capacity</span>
                        <span class="text-sm font-semibold {{ $capText }}">{{ $activeCount }} <span class="text-gray-400">/ 18</span></span>
                    </div>
                    <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-white/10">
                        <div class="h-full rounded-full transition-all {{ $capBar }}" style="width: {{ $capFill }}%"></div>
                    </div>
                    <div class="mt-1.5 flex min-h-4 items-center justify-between text-xs">
                        <span class="text-gray-400 dark:text-gray-500">
                            @if ($benchCount > 0)+ {{ $benchCount }} on bench @endif
                        </span>
                        @if ($overCap > 0)
                            <span class="inline-flex items-center gap-1 font-semibold text-danger-600 dark:text-danger-400">
                                <x-filament::icon icon="heroicon-m-exclamation-triangle" class="h-4 w-4" />
                                {{ $overCap }} over cap
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Active players --}}
        <x-filament::section icon="heroicon-o-check-circle" icon-color="success">
            <x-slot name="heading">Active — {{ $activeCount }} of 18</x-slot>

            @if ($active->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No active players.</p>
            @else
                <ul role="list" class="-my-1 divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($active as $r)
                        <li class="group flex items-center justify-between gap-3 rounded-lg px-2 py-2 hover:bg-gray-50 dark:hover:bg-white/5">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="w-7 shrink-0 text-right text-sm font-bold tabular-nums text-primary-600 dark:text-primary-400">{{ $r->activeSequence }}</span>
                                <span class="truncate font-medium text-gray-900 dark:text-white">{{ $r->memberNameFirst }} {{ $r->memberNameLast }}</span>
                                @if ($r->activeSequence > 18)
                                    <x-filament::badge color="danger" size="sm">Over cap</x-filament::badge>
                                @else
                                    <x-filament::badge color="gray" size="sm">Player {{ $r->activeSequence }} of 18</x-filament::badge>
                                @endif
                            </div>
                            <x-filament::icon-button
                                icon="heroicon-m-user-minus"
                                color="danger"
                                size="sm"
                                label="Deregister"
                                tooltip="Deregister"
                                wire:click="deregister({{ $r->memberID }})"
                                wire:confirm="Deregister {{ $r->memberNameFirst }} {{ $r->memberNameLast }} from this game?"
                            />
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-filament::section>

        {{-- Bench --}}
        <x-filament::section icon="heroicon-o-clock" icon-color="warning">
            <x-slot name="heading">Bench — {{ $benchCount }}</x-slot>

            @if ($bench->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No players on the bench.</p>
            @else
                <ul role="list" class="-my-1 divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($bench as $r)
                        <li class="group flex items-center justify-between gap-3 rounded-lg px-2 py-2 hover:bg-gray-50 dark:hover:bg-white/5">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="w-7 shrink-0 text-right text-sm font-bold tabular-nums text-warning-600 dark:text-warning-400">B{{ $r->benchSequence }}</span>
                                <span class="truncate font-medium text-gray-900 dark:text-white">{{ $r->memberNameFirst }} {{ $r->memberNameLast }}</span>
                            </div>
                            <div class="flex shrink-0 items-center gap-1">
                                <x-filament::icon-button
                                    icon="heroicon-m-arrow-up"
                                    color="gray"
                                    size="sm"
                                    label="Move up"
                                    tooltip="Move up"
                                    :disabled="$r->benchSequence <= 1"
                                    wire:click="moveBench({{ $r->memberID }}, 'up')"
                                />
                                <x-filament::icon-button
                                    icon="heroicon-m-arrow-down"
                                    color="gray"
                                    size="sm"
                                    label="Move down"
                                    tooltip="Move down"
                                    :disabled="$r->benchSequence >= $benchCount"
                                    wire:click="moveBench({{ $r->memberID }}, 'down')"
                                />
                                <x-filament::icon-button
                                    icon="heroicon-m-arrow-up-circle"
                                    color="success"
                                    size="sm"
                                    label="Promote"
                                    tooltip="Promote to active"
                                    wire:click="promote({{ $r->memberID }})"
                                />
                                <x-filament::icon-button
                                    icon="heroicon-m-user-minus"
                                    color="danger"
                                    size="sm"
                                    label="Deregister"
                                    tooltip="Deregister"
                                    wire:click="deregister({{ $r->memberID }})"
                                    wire:confirm="Deregister {{ $r->memberNameFirst }} {{ $r->memberNameLast }} from this game?"
                                />
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-filament::section>

        {{-- Not going --}}
        @if ($notGoing->isNotEmpty())
            <x-filament::section icon="heroicon-o-x-circle" icon-color="danger" collapsible collapsed>
                <x-slot name="heading">Not going — {{ $notGoing->count() }}</x-slot>

                <div class="flex flex-wrap gap-2">
                    @foreach ($notGoing as $r)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-danger-200 bg-danger-50 py-1 pl-3 pr-1 text-sm font-medium text-danger-700 dark:border-danger-400/20 dark:bg-danger-400/10 dark:text-danger-400">
                            {{ $r->memberNameFirst }} {{ $r->memberNameLast }}
                            <x-filament::icon-button
                                icon="heroicon-m-arrow-uturn-left"
                                color="gray"
                                size="sm"
                                label="Re-register"
                                tooltip="Re-register"
                                wire:click="register({{ $r->memberID }})"
                            />
                        </span>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        {{-- Priority pool --}}
        @if ($unregistered->isNotEmpty())
            <x-filament::section
                icon="heroicon-o-user-plus"
                icon-color="gray"
                description="Ordered by games played this season (priority for the final round), then surname."
            >
                <x-slot name="heading">Not yet registered — played this season ({{ $unregistered->count() }})</x-slot>

                <div class="flex flex-wrap gap-2">
                    @foreach ($unregistered as $r)
                        <span class="inline-flex items-center gap-2 rounded-full border border-dashed border-gray-300 py-1 pl-3 pr-1 text-sm font-medium text-gray-600 dark:border-white/20 dark:text-gray-300">
                            {{ $r->memberNameFirst }} {{ $r->memberNameLast }}
                            <x-filament::badge color="gray" size="sm">{{ $r->gamesPlayed }}</x-filament::badge>
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
            </x-filament::section>
        @endif

        {{-- Event log --}}
        <x-filament::section icon="heroicon-o-list-bullet" collapsible>
            <x-slot name="heading">Registration event log</x-slot>

            @if ($events->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    No events recorded for this game.
                    @if ($isNextGame)
                        Event logging is active — all future registration changes will appear here.
                    @else
                        Event logging was introduced after this game was played.
                    @endif
                </p>
            @else
                @php
                    $typeLabels = [
                        'registered'         => ['Registered', 'success'],
                        'deregistered'       => ['Deregistered', 'danger'],
                        'bench_added'        => ['Added to bench', 'warning'],
                        'bench_promoted'     => ['Promoted from bench', 'info'],
                        'admin_registered'   => ['Admin: registered', 'success'],
                        'admin_deregistered' => ['Admin: deregistered', 'danger'],
                    ];
                @endphp
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">
                                <th class="py-2 pr-4 font-medium">When</th>
                                <th class="py-2 pr-4 font-medium">Player</th>
                                <th class="py-2 pr-4 font-medium">Event</th>
                                <th class="py-2 font-medium">Sequence</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                            @foreach ($events as $e)
                                @php
                                    $eventTime = \Carbon\Carbon::parse($e->created_at)->timezone('Australia/Adelaide');
                                    [$label, $badgeColor] = $typeLabels[$e->eventType] ?? [$e->eventType, 'gray'];
                                @endphp
                                <tr>
                                    <td class="whitespace-nowrap py-2 pr-4 text-gray-500 dark:text-gray-400" title="{{ $eventTime->format('Y-m-d H:i:s') }}">
                                        {{ $eventTime->format('D j M, g:ia') }}
                                    </td>
                                    <td class="py-2 pr-4 font-medium text-gray-900 dark:text-white">
                                        {{ $e->memberNameFirst }} {{ $e->memberNameLast }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        <x-filament::badge :color="$badgeColor" size="sm">{{ $label }}</x-filament::badge>
                                    </td>
                                    <td class="py-2">
                                        @if ($e->registrationSequence !== null)
                                            <span class="font-semibold text-primary-600 dark:text-primary-400">Player {{ $e->registrationSequence }} of 18</span>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600">&mdash;</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
