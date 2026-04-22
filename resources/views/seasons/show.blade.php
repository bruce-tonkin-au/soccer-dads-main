@extends('layouts.app')

@section('title', $season->seasonName . ' — Soccer Dads')

@section('content')

<div style="padding:4rem 2rem;">
    <div class="container">

        <a href="/seasons" style="font-size:13px; color:#888; text-decoration:none; display:inline-flex; align-items:center; gap:6px; margin-bottom:1.5rem;">
            <i class="fa-solid fa-chevron-left"></i> All seasons
        </a>

        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:72px; color:#262c39; margin-bottom:0.5rem;">{{ $season->seasonName }}</h1>

        {{-- Stats --}}
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:3rem; margin-top:1.5rem;">
            <div style="background:#262c39; border-radius:12px; padding:1.5rem; text-align:center;">
                <div style="font-size:36px; font-weight:700; color:#fff;">{{ $totalGoals }}</div>
                <div style="font-size:12px; color:rgba(255,255,255,0.5); text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Goals scored</div>
            </div>
            <div style="background:#e68a46; border-radius:12px; padding:1.5rem; text-align:center;">
                <div style="font-size:36px; font-weight:700; color:#fff;">{{ $nights->count() }}</div>
                <div style="font-size:12px; color:rgba(255,255,255,0.7); text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Nights played</div>
            </div>
            <div style="background:#458bc8; border-radius:12px; padding:1.5rem; text-align:center;">
                <div style="font-size:24px; font-weight:700; color:#fff;">{{ $topScorer ? $topScorer->memberNameFirst . ' ' . $topScorer->memberNameLast : 'TBD' }}</div>
                <div style="font-size:12px; color:rgba(255,255,255,0.7); text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Top scorer {{ $topScorer ? '(' . $topScorer->goals . ' goals)' : '' }}</div>
            </div>
        </div>

        {{-- Nights list --}}
        <h2 style="font-size:18px; font-weight:600; color:#262c39; margin-bottom:1rem;">Game nights</h2>
        <div style="display:flex; flex-direction:column; gap:10px;">
            @foreach($nights as $night)
            <a href="/seasons/{{ $season->seasonKey }}/{{ $night->gameRound }}" style="text-decoration:none;">
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