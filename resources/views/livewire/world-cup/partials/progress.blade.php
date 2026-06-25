{{-- Tournament progress accent bar. Driven entirely by wc_fixtures via
     WcPage::tournamentProgress() (cached 60s). Renders nothing before any
     fixtures exist. Shown above the tabs on all four World Cup pages. --}}
@php $wcProgress = $this->tournamentProgress(); @endphp
@if ($wcProgress)
    @php
        $wcDonePct = round($wcProgress['completed'] / $wcProgress['total'] * 100, 2);
        $wcLivePct = round($wcProgress['live'] / $wcProgress['total'] * 100, 2);
    @endphp
    <div class="wc-progress">
        <div class="wc-progress-text">
            <strong>{{ $wcProgress['stage_label'] }}</strong>
            <span class="sep">·</span>
            {{ $wcProgress['completed'] }} of {{ $wcProgress['total'] }} games played
            @if ($wcProgress['next_label'] && $wcProgress['next_start'])
                <span class="sep">·</span>
                {{ $wcProgress['next_label'] }} starts {{ $wcProgress['next_start'] }}
            @endif
        </div>
        <div class="wc-progress-bar">
            <div class="wc-progress-done" style="width:{{ $wcDonePct }}%;"></div>
            @if ($wcProgress['live'] > 0)
                <div class="wc-progress-live" style="width:{{ $wcLivePct }}%;"></div>
            @endif
        </div>
    </div>
@endif
