@extends('layouts.app')

@section('title', $season->seasonName . ' — Soccer Dads')

@section('content')

<div style="padding:4rem 2rem;">
    <div class="container">

        <a href="/seasons" style="font-size:13px; color:#888; text-decoration:none; display:inline-flex; align-items:center; gap:6px; margin-bottom:1.5rem;">
            <i class="fa-solid fa-chevron-left"></i> All seasons
        </a>

        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:72px; color:#262c39; margin-bottom:0.5rem;">{{ $season->seasonName }}</h1>

       {{-- Award winners (completed seasons only) --}}
@if(count($awardWinners) > 0)
<div class="podium-grid" style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:1rem; margin-top:1.5rem;">
    @php
        $podium = [
            ['label' => '1st Place', 'bg' => 'linear-gradient(135deg, #f0c040, #d4a012)', 'icon' => 'fa-trophy'],
            ['label' => '2nd Place', 'bg' => 'linear-gradient(135deg, #aaa, #888)', 'icon' => 'fa-medal'],
            ['label' => '3rd Place', 'bg' => 'linear-gradient(135deg, #cd7f32, #a0522d)', 'icon' => 'fa-medal'],
        ];
    @endphp
    @foreach($awardWinners as $i => $winner)
    <div style="background:{{ $podium[$i]['bg'] }}; border-radius:12px; padding:1.5rem; text-align:center; color:#fff;">
        <i class="fa-solid {{ $podium[$i]['icon'] }}" style="font-size:24px; margin-bottom:8px; display:block;"></i>
        <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.1em; opacity:0.8; margin-bottom:4px;">{{ $podium[$i]['label'] }}</div>
        <div style="font-size:18px; font-weight:700;">
            <a href="/players/{{ $winner->slug }}" style="color:inherit; text-decoration:none; border-bottom:1px solid rgba(255,255,255,0.3);">{{ $winner->name }}</a>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Stats --}}
<div class="stats-grid" style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:3rem; margin-top:1.5rem;">
    <div style="background:#fff; border:1px solid #262c39; border-radius:12px; padding:1rem 1.5rem; text-align:center;">
        <div style="font-size:24px; font-weight:700; color:#262c39;">{{ $nights->count() }}</div>
        <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Nights played</div>
    </div>
    <div style="background:#fff; border:1px solid #262c39; border-radius:12px; padding:1rem 1.5rem; text-align:center;">
        <div style="font-size:24px; font-weight:700; color:#262c39;">{{ $totalGoals }}</div>
        <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Goals scored</div>
    </div>
    <div style="background:#fff; border:1px solid #262c39; border-radius:12px; padding:1rem 1.5rem; text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center;">
        <div style="font-size:18px; font-weight:700; color:#262c39; text-align:center;">{{ $topScorer ? $topScorer->memberNameFirst . ' ' . $topScorer->memberNameLast : 'TBD' }}</div>
        <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px; text-align:center;">Top scorer {{ $topScorer ? '(' . $topScorer->goals . ' goals)' : '' }}</div>
    </div>
</div>

        {{-- Nights list --}}
        <h2 style="font-size:18px; font-weight:600; color:#262c39; margin-bottom:1rem;">Game nights</h2>
        <div style="display:flex; flex-direction:column; gap:10px;">
            @foreach($nights as $night)
            <a href="/seasons/{{ $season->seasonLink }}/{{ $night->gameRound }}" style="text-decoration:none;">
                <div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; padding:1.25rem 1.5rem; display:flex; align-items:center; justify-content:space-between;" onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
                    <div style="display:flex; align-items:center; gap:1rem;">
                        <div style="width:40px; height:40px; background:#f4f4f4; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:700; color:#262c39; font-size:14px;">
                            {{ $night->gameRound }}
                        </div>
                        <div>
                            <div style="font-size:15px; font-weight:500; color:#262c39;">
                                Round {{ $night->gameRound }}
                            </div>
                            <div style="font-size:13px; color:#888;">
                                {{ \Carbon\Carbon::parse($night->gameDate)->format('l j F Y') }}
                            </div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:1rem;">
                        @if($night->hasResults)
<div style="display:flex; gap:8px;">
    @foreach($night->teamGoals as $teamID => $goals)
    @if(isset($night->teams[$teamID]))
    <span style="background:{{ $night->teams[$teamID]['color'] }}; color:#fff; padding:4px 10px; border-radius:20px; font-size:13px; font-weight:600;">
        {{ $goals }}
    </span>
    @endif
    @endforeach
</div>
                        @else
                        <span style="font-size:13px; color:#aaa;">No results yet</span>
                        @endif
                        <i class="fa-solid fa-chevron-right" style="color:#aaa;"></i>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    @media (max-width: 600px) {
        .stats-grid {
            grid-template-columns: 1fr !important;
        }
        .podium-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush