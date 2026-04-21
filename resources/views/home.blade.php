@extends('layouts.app')

@section('title', 'Soccer Dads — Friday Night Futsal Melbourne')

@section('content')

{{-- Hero --}}
<div style="background:#262c39; padding:6rem 2rem; text-align:center;">
    <div style="max-width:700px; margin:0 auto;">
        <img src="/images/Soccer-Dads-Logo.png" style="width:100px; margin-bottom:2rem;">
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:80px; color:#fff; line-height:1; margin-bottom:1.5rem; text-shadow:0 4px 20px rgba(0,0,0,0.3);">
            Soccer Dads
        </h1>
        <p style="font-size:20px; color:rgba(255,255,255,0.7); margin-bottom:2.5rem; line-height:1.6;">
            Futsal for Dads
        </p>
        <a href="/login" class="btn btn-white" style="font-size:16px; padding:14px 32px;">
            <i class="fa-solid fa-right-to-bracket"></i> Player login
        </a>
    </div>
</div>

{{-- Stats bar --}}
<div style="background:linear-gradient(to right, #458bc8, #7bba56, #e68a46); padding:2rem;">
    <div class="container">
        <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:2rem; text-align:center;">
            <div>
                <div style="font-size:36px; font-weight:700; color:#fff;">{{ $stats['seasons'] }}</div>
                <div style="font-size:13px; color:rgba(255,255,255,0.8); text-transform:uppercase; letter-spacing:0.08em;">Seasons</div>
            </div>
            <div>
    <div style="font-size:36px; font-weight:700; color:#fff;">{{ $stats['sessions'] }}</div>
    <div style="font-size:13px; color:rgba(255,255,255,0.8); text-transform:uppercase; letter-spacing:0.08em;">Sessions</div>
</div>
            <div>
                <div style="font-size:36px; font-weight:700; color:#fff;">{{ $stats['games'] }}</div>
                <div style="font-size:13px; color:rgba(255,255,255,0.8); text-transform:uppercase; letter-spacing:0.08em;">Games</div>
            </div>
            <div>
                <div style="font-size:36px; font-weight:700; color:#fff;">{{ $stats['goals'] }}</div>
                <div style="font-size:13px; color:rgba(255,255,255,0.8); text-transform:uppercase; letter-spacing:0.08em;">Goals</div>
            </div>
            <div>
                <div style="font-size:36px; font-weight:700; color:#fff;">{{ $stats['players'] }}</div>
                <div style="font-size:13px; color:rgba(255,255,255,0.8); text-transform:uppercase; letter-spacing:0.08em;">Players</div>
            </div>
        </div>
    </div>
</div>

{{-- Next game --}}
@if(isset($nextGame))
<div style="padding:4rem 2rem; background:#f8f8f8;">
    <div class="container">
        <h2 style="font-family:'GetShow'; font-weight:normal; font-size:48px; color:#262c39; margin-bottom:0.5rem;">Next game</h2>
        <p style="color:#888; font-size:14px; margin-bottom:2rem;">Don't forget to register your attendance!</p>
        <div style="background:#fff; border-radius:16px; padding:2rem; display:flex; align-items:center; justify-content:space-between; box-shadow:0 2px 12px rgba(0,0,0,0.06);">
            <div>
                <div style="font-size:24px; font-weight:600; color:#262c39; margin-bottom:4px;">
                    {{ \Carbon\Carbon::parse($nextGame->gameDate)->format('l j F Y') }}
                </div>
                <div style="font-size:14px; color:#888;">Round {{ $nextGame->gameRound }} · Season {{ $nextGame->seasonName }}</div>
            </div>
            <a href="/r/{{ $nextGame->gameKey }}" class="btn btn-primary">
                <i class="fa-solid fa-futbol"></i> Register attendance
            </a>
        </div>
    </div>
</div>
@endif

{{-- How it works --}}
<div style="padding:5rem 2rem;">
    <div class="container">
        <h2 style="font-family:'GetShow'; font-weight:normal; font-size:56px; color:#262c39; margin-bottom:3rem; text-align:center;">How it works</h2>
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:2rem;">
            <div style="text-align:center; padding:2rem;">
                <div style="width:64px; height:64px; background:#458bc8; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
    <i class="fa-solid fa-envelope" style="color:#fff; font-size:24px;"></i>
</div>
                <h3 style="font-size:18px; font-weight:600; margin-bottom:0.75rem;">Register your attendance</h3>
                <p style="font-size:14px; color:#888; line-height:1.6;">Each week you'll use a personalised link to register your attendance. Tap it to register.</p>
            </div>
            <div style="text-align:center; padding:2rem;">
                <div style="width:64px; height:64px; background:#7bba56; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
                    <i class="fa-solid fa-futbol" style="color:#fff; font-size:24px;"></i>
                </div>
                <h3 style="font-size:18px; font-weight:600; margin-bottom:0.75rem;">Show up and play</h3>
                <p style="font-size:14px; color:#888; line-height:1.6;">Turn up to the session, get assigned to a team and play. Games are five minutes long, two games in a round, seven rounds in a session.</p>
            </div>
            <div style="text-align:center; padding:2rem;">
                <div style="width:64px; height:64px; background:#e68a46; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
                    <i class="fa-solid fa-chart-line" style="color:#fff; font-size:24px;"></i>
                </div>
                <h3 style="font-size:18px; font-weight:600; margin-bottom:0.75rem;">Track your stats</h3>
                <p style="font-size:14px; color:#888; line-height:1.6;">Goals, assists, saves and more are all tracked in real time and available in your player profile.</p>
            </div>
        </div>
    </div>
</div>

@endsection