<div wire:poll.30s>
    @include('livewire.world-cup.partials.styles')

    <div class="wc-page">
        <div class="wc-hero" style="background:linear-gradient(to right, #458bc8, #7bba56, #e68a46);">
            <div class="container">
                <h1 style="color:#fff;">World Cup 2026</h1>
            </div>
        </div>

        <div class="wc-wrap">
            @include('livewire.world-cup.partials.live')
            @include('livewire.world-cup.partials.tabs')

            @if ($ladder->isEmpty())
                <div class="wc-empty">
                    <div class="wc-emoji">📋</div>
                    <h3>No entries yet</h3>
                    <p>Entries appear here as people join the sweepstake.</p>
                </div>
            @else
                <div class="wc-tablewrap">
                    <table class="wc-table">
                        <thead>
                            <tr>
                                <th style="text-align:center;">#</th>
                                <th>Entry</th>
                                <th>Top 24</th>
                                <th>Bottom 24</th>
                                <th>Players</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ladder as $row)
                                <tr class="@if($row['position'] && $row['position'] <= 3) wc-r{{ $row['position'] }} @endif">
                                    <td class="wc-pos">
                                        @if (! $row['position'])
                                            <span class="wc-muted">—</span>
                                        @else
                                            @switch($row['position'])
                                                @case(1) 🥇 @break
                                                @case(2) 🥈 @break
                                                @case(3) 🥉 @break
                                                @default {{ $row['position'] }}
                                            @endswitch
                                        @endif
                                    </td>
                                    <td><span class="wc-entry">{{ $row['member_name'] }}</span></td>
                                    @foreach (['top_team', 'bottom_team'] as $key)
                                        <td>
                                            @php $team = $row[$key]; @endphp
                                            @if ($team)
                                                <div class="wc-line">
                                                    <span class="flag">{{ $team['flag'] }}</span>
                                                    <span>{{ $team['name'] }}</span>
                                                    @if ($team['group_letter'])<span class="grp">{{ $team['group_letter'] }}</span>@endif
                                                </div>
                                            @else
                                                <span class="wc-muted">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td>
                                        @forelse ($row['players'] as $player)
                                            <div class="wc-line">
                                                <span class="flag">{{ $player['flag'] }}</span>
                                                <span>{{ $player['name'] }}</span>
                                                @if ($player['goal_count'] > 0)<span class="wc-badge">⚽ {{ $player['goal_count'] }}</span>@endif
                                            </div>
                                        @empty
                                            <span class="wc-muted">—</span>
                                        @endforelse
                                    </td>
                                    <td class="wc-pts"><span class="wc-total">{{ $row['total_points'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="wc-key">
                Team goal = <strong>{{ $pointsKey['team_goal'] }}pt{{ $pointsKey['team_goal'] == 1 ? '' : 's' }}</strong>
                · Player goal = <strong>{{ $pointsKey['player_goal'] }}pt{{ $pointsKey['player_goal'] == 1 ? '' : 's' }}</strong>
            </div>
        </div>
    </div>
</div>
