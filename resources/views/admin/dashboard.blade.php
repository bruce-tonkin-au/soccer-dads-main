@extends('admin.layout')
@section('title', 'Dashboard')
@section('content')

<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:2rem;">
    <div class="admin-card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:36px; font-weight:700; color:#458bc8;">{{ $stats['players'] }}</div>
        <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Active players</div>
    </div>
    <div class="admin-card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:36px; font-weight:700; color:#7bba56;">{{ $stats['seasons'] }}</div>
        <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Seasons</div>
    </div>
    <div class="admin-card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:36px; font-weight:700; color:#e68a46;">{{ $stats['games'] }}</div>
        <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Game nights</div>
    </div>
    <div class="admin-card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:36px; font-weight:700; color:#262c39;">{{ $stats['goals'] }}</div>
        <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Goals scored</div>
    </div>
</div>

@if($nextGame)
<div class="admin-card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
        <h2 style="margin-bottom:0;">Next game — {{ \Carbon\Carbon::parse($nextGame->gameDate)->format('l j F Y') }}</h2>
        <div style="display:flex; gap:8px;">
            <a href="/admin/teams/{{ $nextGame->gameID }}" class="btn btn-primary">
                <i class="fa-solid fa-users"></i> Manage teams
            </a>
            <a href="/admin/seasons/{{ $nextGame->gameSeason }}/games/{{ $nextGame->gameID }}/edit" class="btn btn-secondary">
                <i class="fa-solid fa-pen"></i> Edit game
            </a>
        </div>
    </div>
    <p style="font-size:13px; color:#888; margin-bottom:1rem;">{{ $registrations?->count() ?? 0 }} players registered</p>
    @if($registrations && $registrations->count() > 0)
    <div style="display:flex; flex-wrap:wrap; gap:8px;">
        @foreach($registrations as $r)
        <a href="/admin/players/{{ $r->memberID }}/edit" style="background:#f4f4f4; border-radius:20px; padding:6px 14px; font-size:13px; color:#262c39; text-decoration:none;">
            {{ $r->memberNameFirst }} {{ $r->memberNameLast }}
        </a>
        @endforeach
    </div>
    @endif
</div>
@endif

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
    <div class="admin-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h2 style="margin-bottom:0;">Quick links</h2>
        </div>
        <div style="display:flex; flex-direction:column; gap:8px;">
            <a href="/admin/players/create" class="btn btn-primary" style="justify-content:flex-start;">
                <i class="fa-solid fa-user-plus"></i> Add new player
            </a>
            <a href="/admin/players" class="btn btn-secondary" style="justify-content:flex-start;">
                <i class="fa-solid fa-users"></i> Manage players
            </a>
            <a href="/admin/seasons" class="btn btn-secondary" style="justify-content:flex-start;">
                <i class="fa-solid fa-calendar"></i> Manage seasons
            </a>
        </div>
    </div>
    <div class="admin-card">
        <h2>System</h2>
        <div style="font-size:14px; color:#888; line-height:2;">
            <div>Scoring system: <a href="http://localhost:8000" target="_blank" style="color:#458bc8;">localhost:8000</a></div>
            <div>Main site: <a href="http://localhost:8001" target="_blank" style="color:#458bc8;">localhost:8001</a></div>
        </div>
    </div>
</div>

@endsection
