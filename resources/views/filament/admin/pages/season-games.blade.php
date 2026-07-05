<x-filament-panels::page>
    {{-- Back to Seasons --}}
    <div>
        <a href="{{ \App\Filament\Admin\Resources\Seasons\SeasonResource::getUrl() }}"
           class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            ← Seasons
        </a>
    </div>

    {{-- Games --}}
    <x-filament::section>
        <div class="flex items-center justify-between gap-4">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Games</h3>
            {{ $this->createGameAction }}
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">
                        <th class="py-2 pr-4 font-medium">Round</th>
                        <th class="py-2 pr-4 font-medium">Date</th>
                        <th class="py-2 pr-4 font-medium">YouTube</th>
                        <th class="py-2 pr-4 font-medium">Visible</th>
                        <th class="py-2 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse ($games as $game)
                        @php
                            $isChargeable   = $game->gameDate >= '2026-05-01';
                            $hasTeams       = $isChargeable && $gamesWithTeams->contains($game->gameID);
                            $alreadyCharged = $isChargeable && $chargedGameIDs->contains($game->gameID);
                        @endphp
                        <tr class="text-gray-950 dark:text-white">
                            <td class="py-2 pr-4 font-semibold whitespace-nowrap">Round {{ $game->gameRound }}</td>
                            <td class="py-2 pr-4 whitespace-nowrap">{{ $game->gameDate }}</td>
                            <td class="py-2 pr-4">{{ $game->gameYouTube ? '✓' : '—' }}</td>
                            <td class="py-2 pr-4">
                                @if ($game->gameVisible)
                                    <x-filament::badge color="success">Yes</x-filament::badge>
                                @else
                                    <x-filament::badge color="gray">No</x-filament::badge>
                                @endif
                            </td>
                            <td class="py-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    {{-- Teams — not migrated; links out to /admin --}}
                                    <x-filament::button tag="a" href="/admin/teams/{{ $game->gameID }}" target="_blank"
                                        color="gray" size="xs" icon="heroicon-o-users">
                                        Teams
                                    </x-filament::button>

                                    {{-- Registrations — the /manage page, deep-linked to this game --}}
                                    <x-filament::button tag="a"
                                        :href="\App\Filament\Admin\Pages\Registrations::getUrl(['gameID' => $game->gameID])"
                                        color="gray" size="xs" icon="heroicon-o-clipboard-document-check">
                                        Registrations
                                    </x-filament::button>

                                    {{-- Print — not migrated; links out to /admin --}}
                                    <x-filament::button tag="a" href="/admin/print/{{ $game->gameID }}" target="_blank"
                                        color="gray" size="xs" icon="heroicon-o-printer">
                                        Print
                                    </x-filament::button>

                                    {{-- Edit — opens the edit modal on this page --}}
                                    {{ ($this->editGameAction)(['gameID' => $game->gameID]) }}

                                    {{-- Charge cell: indicator, or link out to the legacy charge flow --}}
                                    @if ($alreadyCharged)
                                        <x-filament::badge color="success" icon="heroicon-o-check-circle">Charged</x-filament::badge>
                                    @elseif ($hasTeams)
                                        <x-filament::button tag="a" href="/admin/seasons/{{ $seasonID }}/games" target="_blank"
                                            color="warning" size="xs" icon="heroicon-o-currency-dollar">
                                            Charge players
                                        </x-filament::button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500 dark:text-gray-400">
                                No games yet for this season.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

    {{-- Season ladder --}}
    <x-filament::section>
        <div class="flex items-baseline justify-between gap-4">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Season ladder</h3>
            <span class="text-xs text-gray-500 dark:text-gray-400">Sorted by average points per game</span>
        </div>

        @if ($ladder->isNotEmpty())
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Title contenders must have played {{ $threshold }} {{ \Illuminate\Support\Str::plural('game', $threshold) }}
                (season average {{ number_format($avgGamesPlayed, 1) }} rounded up).
                Tied averages are split by total team points earned across nights played.
            </p>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <th class="py-2 pr-4 font-medium" style="width:48px;">Rank</th>
                            <th class="py-2 pr-4 font-medium">Player</th>
                            <th class="py-2 pr-4 text-center font-medium">Games played</th>
                            <th class="py-2 pr-4 text-center font-medium">Total points</th>
                            <th class="py-2 pr-4 text-right font-medium">Average</th>
                            <th class="py-2 text-center font-medium">Team pts</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($ladder as $i => $p)
                            <tr @class([
                                'text-gray-950 dark:text-white' => $p->eligible,
                                'text-gray-400 dark:text-gray-600' => ! $p->eligible,
                            ])>
                                <td class="py-2 pr-4 font-semibold">{{ $i + 1 }}</td>
                                <td class="py-2 pr-4">
                                    {{ $p->name }}
                                    @unless ($p->eligible)
                                        <span class="ml-2 whitespace-nowrap rounded-full border border-danger-200 bg-danger-50 px-2 py-0.5 text-xs text-danger-600 dark:border-danger-500/30 dark:bg-danger-500/10 dark:text-danger-400">
                                            ineligible — {{ $p->gamesPlayed }} {{ \Illuminate\Support\Str::plural('game', $p->gamesPlayed) }}
                                        </span>
                                    @endunless
                                </td>
                                <td class="py-2 pr-4 text-center">{{ $p->gamesPlayed }}</td>
                                <td class="py-2 pr-4 text-center">{{ $p->totalPoints }}</td>
                                <td class="py-2 pr-4 text-right font-semibold">{{ number_format($p->average, 2) }}</td>
                                <td class="py-2 text-center">{{ $p->teamPoints }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No results recorded for this season yet.</p>
        @endif
    </x-filament::section>

    {{-- Average progression chart (title-eligible players only) --}}
    @if (! empty($chartSeries))
        <x-filament::section>
            <div class="flex items-baseline justify-between gap-4">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Average progression</h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">Title-eligible players only ({{ count($chartSeries) }} plotted)</span>
            </div>

            <div class="mt-4"
                x-data="{
                    labels: @js($chartLabels),
                    series: @js($chartSeries),
                    chart: null,
                    build() {
                        if (! window.Chart) return;
                        if (this.chart) this.chart.destroy();
                        const datasets = this.series.map((s, i) => {
                            const color = 'hsl(' + Math.round((i * 360) / this.series.length) + ', 65%, 50%)';
                            return { label: s.name, data: s.data, borderColor: color, backgroundColor: color,
                                     borderWidth: 2, pointRadius: 2, tension: 0.2, fill: false, spanGaps: false };
                        });
                        this.chart = new Chart(this.$refs.canvas.getContext('2d'), {
                            type: 'line',
                            data: { labels: this.labels, datasets: datasets },
                            options: {
                                responsive: true,
                                interaction: { mode: 'nearest', intersect: false },
                                plugins: {
                                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                                    tooltip: { mode: 'index', intersect: false },
                                },
                                scales: {
                                    y: { title: { display: true, text: 'Average points / game' }, suggestedMin: 1, suggestedMax: 3 },
                                    x: { title: { display: true, text: 'Round' } },
                                },
                            },
                        });
                    },
                    init() {
                        if (window.Chart) { this.build(); return; }
                        const s = document.createElement('script');
                        s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                        s.onload = () => this.build();
                        document.head.appendChild(s);
                    },
                }">
                <canvas x-ref="canvas" height="120"></canvas>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
