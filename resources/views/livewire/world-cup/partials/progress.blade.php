{{-- Tournament stage timeline + current-stage progress bar. Driven entirely by
     wc_fixtures via WcPage::tournamentProgress() (cached 60s). Stages only show
     once they have fixture rows. Renders nothing before any fixtures exist.
     Inline styles only (no Tailwind); flex-wrap keeps the timeline mobile-safe.
     Shown above the tabs on all four World Cup pages. --}}
@php $wcProgress = $this->tournamentProgress(); @endphp
@if ($wcProgress)
    @php
        $wcStages = $wcProgress['stages'];
        $wcTotal = $wcProgress['current_total'];
        $wcDonePct = $wcTotal > 0 ? round($wcProgress['current_completed'] / $wcTotal * 100, 2) : 0;
        $wcLivePct = $wcTotal > 0 ? round($wcProgress['current_live'] / $wcTotal * 100, 2) : 0;

        // Per-state pill styling. Completed = solid brand gradient, current =
        // highlighted blue outline, future = muted grey.
        $wcNodeStyle = [
            'completed' => 'background:linear-gradient(135deg,#3b82c4,#4aaa6e);color:#fff;border:1px solid transparent;',
            'current'   => 'background:#eef4fb;color:#1f6fb0;border:1px solid #3b82c4;font-weight:800;box-shadow:0 1px 3px rgba(59,130,196,.22);',
            'future'    => 'background:#f1f2f4;color:#9aa1ad;border:1px solid #eceef1;',
        ];
    @endphp
    <div class="wc-progress">
        {{-- Stage timeline: pills joined by connector lines. A connector is
             green once the stage on its left is fully completed. --}}
        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:6px;row-gap:10px;margin-bottom:14px;">
            @foreach ($wcStages as $i => $stage)
                @if ($i > 0)
                    <div style="flex:1 1 16px;min-width:12px;height:2px;border-radius:2px;background:{{ $wcStages[$i - 1]['state'] === 'completed' ? '#4aaa6e' : '#e3e5e9' }};"></div>
                @endif
                <div style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:999px;font-size:12.5px;letter-spacing:.01em;white-space:nowrap;{{ $wcNodeStyle[$stage['state']] }}">
                    @if ($stage['state'] === 'completed')
                        <span style="font-size:11px;line-height:1;">✓</span>
                    @elseif ($stage['state'] === 'current')
                        <span style="width:7px;height:7px;border-radius:50%;background:#3b82c4;{{ $wcProgress['current_live'] > 0 ? 'animation:wc-prog-pulse 1.2s ease-in-out infinite;' : '' }}"></span>
                    @endif
                    <span>{{ $stage['short'] }}</span>
                </div>
            @endforeach
        </div>

        {{-- Current-stage summary + progress bar. --}}
        <div class="wc-progress-text">
            <strong>{{ $wcProgress['stage_label'] }}</strong>
            <span class="sep">·</span>
            {{ $wcProgress['current_completed'] }} of {{ $wcProgress['current_total'] }} games played
            @if ($wcProgress['next_label'] && $wcProgress['next_start'])
                <span class="sep">·</span>
                {{ $wcProgress['next_label'] }} starts {{ $wcProgress['next_start'] }}
            @endif
        </div>
        <div class="wc-progress-bar">
            <div class="wc-progress-done" style="width:{{ $wcDonePct }}%;"></div>
            @if ($wcProgress['current_live'] > 0)
                <div class="wc-progress-live" style="width:{{ $wcLivePct }}%;"></div>
            @endif
        </div>
    </div>
@endif
