<x-filament-panels::page>
    @php
        $total = (int) ($this->averages['total'] ?? 0);
        $cards = [
            ['label' => 'Goal',      'value' => $this->averages['avgGoal'] ?? null,      'color' => 'text-green-600 dark:text-green-400'],
            ['label' => 'Passing',   'value' => $this->averages['avgPassing'] ?? null,   'color' => 'text-blue-600 dark:text-blue-400'],
            ['label' => 'Work',      'value' => $this->averages['avgWork'] ?? null,      'color' => 'text-orange-600 dark:text-orange-400'],
            ['label' => 'Defending', 'value' => $this->averages['avgDefending'] ?? null, 'color' => 'text-purple-600 dark:text-purple-400'],
            ['label' => 'Overall',   'value' => $this->averages['avgOverall'] ?? null,   'color' => 'text-amber-600 dark:text-amber-400'],
        ];
    @endphp

    @if ($total > 0)
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
            @foreach ($cards as $card)
                <div class="fi-section rounded-xl bg-white p-6 text-center shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="text-3xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
                    <div class="mt-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {{ $card['label'] }} / 4
                    </div>
                </div>
            @endforeach
        </div>

        <x-filament::section>
            <x-slot name="heading">Individual ratings ({{ $total }})</x-slot>

            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-gray-500 dark:border-white/10 dark:text-gray-400">
                        <th class="py-2 pr-3 font-medium">Rated by</th>
                        <th class="px-3 py-2 text-center font-medium">Goal</th>
                        <th class="px-3 py-2 text-center font-medium">Passing</th>
                        <th class="px-3 py-2 text-center font-medium">Work</th>
                        <th class="px-3 py-2 text-center font-medium">Defending</th>
                        <th class="px-3 py-2 text-center font-medium">Overall</th>
                        <th class="py-2 pl-3 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->ratings as $r)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-2 pr-3">{{ trim(($r['memberNameFirst'] ?? '') . ' ' . ($r['memberNameLast'] ?? '')) }}</td>
                            <td class="px-3 py-2 text-center font-semibold">{{ $r['ratingGoal'] }}</td>
                            <td class="px-3 py-2 text-center font-semibold">{{ $r['ratingPassing'] }}</td>
                            <td class="px-3 py-2 text-center font-semibold">{{ $r['ratingWork'] }}</td>
                            <td class="px-3 py-2 text-center font-semibold">{{ $r['ratingDefending'] }}</td>
                            <td class="px-3 py-2 text-center font-semibold">{{ $r['ratingOverall'] }}</td>
                            <td class="py-2 pl-3 text-gray-500 dark:text-gray-400">
                                {{ $r['created_at'] ? \Carbon\Carbon::parse($r['created_at'])->format('j M Y') : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-filament::section>
    @else
        <x-filament::section>
            <div class="py-12 text-center text-gray-400">
                <x-filament::icon icon="heroicon-o-star" class="mx-auto mb-4 h-12 w-12" />
                No ratings yet for this player.
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
