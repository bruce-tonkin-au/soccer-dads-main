<div wire:poll.30s>
    @include('livewire.world-cup.partials.styles')

    <div class="wc-page">
        <x-page-header title="World Cup 2026" />

        <div class="wc-wrap">
            @include('livewire.world-cup.partials.live')
            @include('livewire.world-cup.partials.tabs')

            @if ($upcoming->isEmpty())
                <div class="wc-empty">
                    <div class="wc-emoji">📅</div>
                    <h3>No upcoming fixtures</h3>
                    <p>Every scheduled match has been played.</p>
                </div>
            @else
                <div class="wc-cards">
                    @foreach ($upcoming as $fixture)
                        <div class="wc-card">
                            <div class="wc-row">
                                <div class="wc-teams">
                                    <span>{{ $fixture['home_flag'] }} {{ $fixture['home_name'] }}</span>
                                    <span class="vs">VS</span>
                                    <span>{{ $fixture['away_flag'] }} {{ $fixture['away_name'] }}</span>
                                </div>
                                <div class="wc-meta">
                                    @if ($fixture['group_letter'])<div class="wc-grp-pill">Group {{ $fixture['group_letter'] }}</div>@endif
                                    <div class="wc-when">
                                        {{ optional($fixture['datetime'])->copy()?->timezone('Australia/Adelaide')?->format('D j M, g:ia') }} ACST
                                    </div>
                                </div>
                            </div>

                            @if (! empty($fixture['team_watchers']) || ! empty($fixture['player_watchers']))
                                <div class="wc-block">
                                    @if (! empty($fixture['team_watchers']))
                                        <div class="wc-watch">
                                            🎯
                                            @foreach ($fixture['team_watchers'] as $w)
                                                <strong>{{ $w['name'] }}</strong> ({{ $w['team'] }}){{ ! $loop->last ? ' · ' : '' }}
                                            @endforeach
                                        </div>
                                    @endif
                                    @if (! empty($fixture['player_watchers']))
                                        <div class="wc-watch" style="margin-top:6px;">
                                            ⚽
                                            @foreach ($fixture['player_watchers'] as $w)
                                                <strong>{{ $w['name'] }}</strong> ({{ $w['player'] }}){{ ! $loop->last ? ' · ' : '' }}
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
