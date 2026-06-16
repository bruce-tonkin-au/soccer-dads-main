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

            @if ($results->isEmpty())
                <div class="wc-empty">
                    <div class="wc-emoji">⚽</div>
                    <h3>No results yet</h3>
                    <p>No results yet — first match kicks off Fri 12 Jun, 4:30am ACST</p>
                </div>
            @else
                <div class="wc-cards">
                    @foreach ($results as $result)
                        <div class="wc-card">
                            <div class="wc-row">
                                <div class="wc-teams">
                                    <span>{{ $result['home_flag'] }} {{ $result['home_name'] }}</span>
                                    <span class="score">{{ $result['home_score'] }} : {{ $result['away_score'] }}</span>
                                    <span>{{ $result['away_flag'] }} {{ $result['away_name'] }}</span>
                                </div>
                                <div class="wc-meta">
                                    @if ($result['group_letter'])<div class="wc-grp-pill">Group {{ $result['group_letter'] }}</div>@endif
                                    <div class="wc-when">{{ optional($result['date'])->copy()?->timezone('Australia/Adelaide')?->format('D j M') }}</div>
                                </div>
                            </div>

                            @if ($result['scorers'] !== '')
                                <div class="wc-scorers">⚽ {{ $result['scorers'] }}</div>
                            @endif

                            @if ($result['cards'] !== '')
                                <div class="wc-scorers">{{ $result['cards'] }}</div>
                            @endif

                            @if (! empty($result['awards']))
                                <div class="wc-block">
                                    <div class="lbl">Points awarded</div>
                                    @foreach ($result['awards'] as $award)
                                        <div class="wc-award">
                                            <strong>{{ $award['member_name'] }}</strong>
                                            <span class="pts">+{{ $award['points'] }}</span>
                                            <span class="wc-muted">({{ $award['reason'] }})</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
