{{-- LIVE NOW — only when a fixture is in play. Shows on every World Cup page. --}}
@if (! $liveFixtures->isEmpty())
    <div class="wc-live">
        <div class="wc-live-grid">
            @foreach ($liveFixtures as $fixture)
                <div class="wc-live-card">
                    <div class="wc-live-top">
                        <span class="wc-live-badge"><span class="dot"></span> Live</span>
                        @if ($fixture['group_letter'])<span class="wc-grp-pill">Group {{ $fixture['group_letter'] }}</span>@endif
                    </div>

                    <div class="wc-live-score">
                        <span class="wc-live-team"><span class="flag">{{ $fixture['home_flag'] }}</span> {{ $fixture['home_name'] }}</span>
                        <span class="wc-live-num">{{ $fixture['home_score'] }} : {{ $fixture['away_score'] }}</span>
                        <span class="wc-live-team"><span class="flag">{{ $fixture['away_flag'] }}</span> {{ $fixture['away_name'] }}</span>
                    </div>

                    @if ($fixture['scorers'] !== '')
                        <div class="wc-live-scorers">⚽ {{ $fixture['scorers'] }}</div>
                    @endif

                    @php
                        $teamWatchers = [];
                        foreach ($fixture['team_stakes'] as $stake) {
                            foreach ($stake['names'] as $name) {
                                $teamWatchers[] = ['name' => $name, 'team' => $stake['team']];
                            }
                        }
                        $playerWatchers = [];
                        foreach ($fixture['player_stakes'] as $stake) {
                            foreach ($stake['names'] as $name) {
                                $playerWatchers[] = ['name' => $name, 'player' => $stake['player']];
                            }
                        }
                    @endphp
                    @if (! empty($teamWatchers) || ! empty($playerWatchers))
                        <div class="wc-live-stakes">
                            @if (! empty($teamWatchers))
                                <div class="wc-watch">
                                    🎯
                                    @foreach ($teamWatchers as $w)
                                        <strong>{{ $w['name'] }}</strong> ({{ $w['team'] }}){{ ! $loop->last ? ' · ' : '' }}
                                    @endforeach
                                </div>
                            @endif
                            @if (! empty($playerWatchers))
                                <div class="wc-watch" style="margin-top:6px;">
                                    ⚽
                                    @foreach ($playerWatchers as $w)
                                        <strong>{{ $w['name'] }}</strong> ({{ $w['player'] }}){{ ! $loop->last ? ' · ' : '' }}
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
