<x-filament-panels::page>
    {{-- Game selector --}}
    <div class="flex items-center gap-3">
        <label class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Game</label>
        <select
            wire:model.live="gameID"
            class="block w-full max-w-md rounded-lg border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white"
        >
            @foreach ($allGames as $g)
                <option value="{{ $g->gameID }}">
                    {{ $g->seasonName }} — Round {{ $g->gameRound }}
                    ({{ \Carbon\Carbon::parse($g->gameDate)->format('d M Y') }})@if ($nextGame && $g->gameID == $nextGame->gameID) ★ Next game @endif
                </option>
            @endforeach
        </select>
    </div>

    @if (! $game)
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">No games found.</p>
        </x-filament::section>
    @else
        @php
            $capClasses = [
                'success' => ['text' => 'text-success-600 dark:text-success-400', 'bg' => 'bg-success-500'],
                'warning' => ['text' => 'text-warning-600 dark:text-warning-400', 'bg' => 'bg-warning-500'],
                'danger'  => ['text' => 'text-danger-600 dark:text-danger-400',  'bg' => 'bg-danger-500'],
            ][$capColor];
        @endphp

        {{-- Game header + capacity --}}
        <x-filament::section>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="text-lg font-bold text-gray-950 dark:text-white">
                        {{ $game->seasonName }} — Round {{ $game->gameRound }}
                    </div>
                    <div class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                        {{ \Carbon\Carbon::parse($game->gameDate)->format('l j F Y') }}
                        &nbsp;·&nbsp; Game ID: {{ $game->gameID }}
                        @if ($isNextGame)
                            &nbsp;·&nbsp; <span class="font-semibold text-primary-600 dark:text-primary-400">★ Next game</span>
                        @endif
                    </div>
                </div>
                <div class="min-w-40 text-right">
                    <div class="text-3xl font-bold {{ $capClasses['text'] }}">
                        {{ $activeCount }}<span class="text-base text-gray-400">/18</span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">active players</div>
                    <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-white/10">
                        <div class="h-full rounded-full {{ $capClasses['bg'] }}" style="width: {{ $capFill }}%"></div>
                    </div>
                    @if ($benchCount > 0)
                        <div class="mt-1 text-xs text-warning-600 dark:text-warning-400">+ {{ $benchCount }} on bench</div>
                    @endif
                    @if ($overCap > 0)
                        <div class="mt-1 text-xs font-semibold text-danger-600 dark:text-danger-400">
                            {{ $overCap }} over cap
                        </div>
                    @endif
                </div>
            </div>
        </x-filament::section>

        {{-- Active players --}}
        <x-filament::section>
            <x-slot name="heading">Active — {{ $activeCount }} of 18</x-slot>

            @if ($active->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No active players.</p>
            @else
                <ul role="list" class="divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($active as $r)
                        <li class="flex items-center justify-between gap-3 py-2">
                            <div class="flex items-center gap-3">
                                <span class="w-7 text-right font-bold text-primary-600 dark:text-primary-400">{{ $r->activeSequence }}</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $r->memberNameFirst }} {{ $r->memberNameLast }}</span>
                                @if ($r->activeSequence > 18)
                                    <x-filament::badge color="danger">Over cap</x-filament::badge>
                                @else
                                    <x-filament::badge color="success">Player {{ $r->activeSequence }} of 18</x-filament::badge>
                                @endif
                            </div>
                            <x-filament::button
                                size="xs"
                                color="danger"
                                icon="heroicon-m-user-minus"
                                wire:click="deregister({{ $r->memberID }})"
                                wire:confirm="Deregister {{ $r->memberNameFirst }} {{ $r->memberNameLast }} from this game?"
                            >
                                Deregister
                            </x-filament::button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-filament::section>

        {{-- Bench --}}
        <x-filament::section>
            <x-slot name="heading">Bench — {{ $benchCount }}</x-slot>

            @if ($bench->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No players on the bench.</p>
            @else
                <ul role="list" class="divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($bench as $r)
                        <li class="flex items-center justify-between gap-3 py-2">
                            <div class="flex items-center gap-3">
                                <span class="w-7 text-right font-bold text-warning-600 dark:text-warning-400">B{{ $r->benchSequence }}</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $r->memberNameFirst }} {{ $r->memberNameLast }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <x-filament::button
                                    size="xs"
                                    color="gray"
                                    icon="heroicon-m-arrow-up"
                                    :disabled="$r->benchSequence <= 1"
                                    wire:click="moveBench({{ $r->memberID }}, 'up')"
                                />
                                <x-filament::button
                                    size="xs"
                                    color="gray"
                                    icon="heroicon-m-arrow-down"
                                    :disabled="$r->benchSequence >= $benchCount"
                                    wire:click="moveBench({{ $r->memberID }}, 'down')"
                                />
                                <x-filament::button
                                    size="xs"
                                    color="success"
                                    icon="heroicon-m-arrow-up-circle"
                                    wire:click="promote({{ $r->memberID }})"
                                >
                                    Promote
                                </x-filament::button>
                                <x-filament::button
                                    size="xs"
                                    color="danger"
                                    icon="heroicon-m-user-minus"
                                    wire:click="deregister({{ $r->memberID }})"
                                    wire:confirm="Deregister {{ $r->memberNameFirst }} {{ $r->memberNameLast }} from this game?"
                                >
                                    Deregister
                                </x-filament::button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-filament::section>

        {{-- Not going --}}
        @if ($notGoing->isNotEmpty())
            <x-filament::section collapsible collapsed>
                <x-slot name="heading">Not going — {{ $notGoing->count() }}</x-slot>

                <div class="flex flex-wrap gap-2">
                    @foreach ($notGoing as $r)
                        <span class="inline-flex items-center gap-2 rounded-full border border-danger-200 bg-danger-50 px-3 py-1 text-sm text-danger-700 dark:border-danger-400/20 dark:bg-danger-400/10 dark:text-danger-400">
                            {{ $r->memberNameFirst }} {{ $r->memberNameLast }}
                            <button
                                type="button"
                                title="Re-register"
                                class="text-danger-400 hover:text-success-600"
                                wire:click="register({{ $r->memberID }})"
                            >
                                <x-filament::icon icon="heroicon-m-arrow-uturn-left" class="h-4 w-4" />
                            </button>
                        </span>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        {{-- Priority pool --}}
        @if ($unregistered->isNotEmpty())
            <x-filament::section description="Ordered by games played this season (priority for the final round), then surname.">
                <x-slot name="heading">Not yet registered — played this season ({{ $unregistered->count() }})</x-slot>

                <div class="flex flex-wrap gap-2">
                    @foreach ($unregistered as $r)
                        <span class="inline-flex items-center gap-2 rounded-full border border-dashed border-gray-300 py-1 pl-3 pr-1 text-sm text-gray-500 dark:border-white/20 dark:text-gray-400">
                            {{ $r->memberNameFirst }} {{ $r->memberNameLast }}
                            <span title="Games played this season" class="text-xs font-bold text-gray-400">{{ $r->gamesPlayed }}</span>
                            <button
                                type="button"
                                title="Register for game"
                                class="text-gray-400 hover:text-success-600"
                                wire:click="register({{ $r->memberID }})"
                            >
                                <x-filament::icon icon="heroicon-m-arrow-up" class="h-4 w-4" />
                            </button>
                        </span>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        {{-- Event log --}}
        <x-filament::section collapsible>
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
                            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                <th class="py-2 pr-4 font-semibold">When</th>
                                <th class="py-2 pr-4 font-semibold">Player</th>
                                <th class="py-2 pr-4 font-semibold">Event</th>
                                <th class="py-2 font-semibold">Sequence</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                            @foreach ($events as $e)
                                @php
                                    $eventTime = \Carbon\Carbon::parse($e->created_at)->timezone('Australia/Adelaide');
                                    [$label, $badgeColor] = $typeLabels[$e->eventType] ?? [$e->eventType, 'gray'];
                                @endphp
                                <tr>
                                    <td class="py-2 pr-4 whitespace-nowrap text-gray-500 dark:text-gray-400" title="{{ $eventTime->format('Y-m-d H:i:s') }}">
                                        {{ $eventTime->format('D j M, g:ia') }}
                                    </td>
                                    <td class="py-2 pr-4 font-medium text-gray-900 dark:text-white">
                                        {{ $e->memberNameFirst }} {{ $e->memberNameLast }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        <x-filament::badge :color="$badgeColor">{{ $label }}</x-filament::badge>
                                    </td>
                                    <td class="py-2">
                                        @if ($e->registrationSequence !== null)
                                            <span class="font-semibold text-primary-600 dark:text-primary-400">Player {{ $e->registrationSequence }} of 18</span>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600">—</span>
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
