@extends('layouts.app')

@section('title', 'Soccer Dads — Game Registration')

@section('content')

<div style="max-width:560px; margin:3rem auto; padding:0 1.5rem;">

    {{-- Header --}}
    <div style="text-align:center; margin-bottom:2rem;">
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:56px; color:#262c39; margin-bottom:0.25rem;">
            {{ $member->memberNameFirst }}!
        </h1>
        <p style="font-size:14px; color:#888;">Game registration</p>
    </div>

    @if(session('success'))
    <div style="background:#f0fdf4; border:1px solid #7bba56; border-radius:8px; padding:12px 16px; margin-bottom:1.5rem; font-size:14px; color:#262c39;">
        <i class="fa-solid fa-circle-check" style="color:#7bba56;"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
<div style="background:#fff3f3; border:1px solid #e24b4a; border-radius:8px; padding:12px 16px; margin-bottom:1.5rem; font-size:14px; color:#262c39;">
    <i class="fa-solid fa-circle-exclamation" style="color:#e24b4a;"></i> {{ session('error') }}
</div>
@endif

    {{-- Balance warning --}}
    @if($balance < -20)
    <div style="background:#fff3f3; border:1px solid #e24b4a; border-radius:8px; padding:12px 16px; margin-bottom:1.5rem; font-size:14px; color:#262c39;">
        <i class="fa-solid fa-triangle-exclamation" style="color:#e24b4a;"></i>
        Your balance is <strong>${{ number_format($balance, 2) }}</strong>. Please top up to register for future games.
        <a href="/p/{{ $member->memberCode }}" style="color:#e24b4a; font-weight:600; margin-left:8px;">Top up →</a>
    </div>
    @endif

    {{-- Game details --}}
    <div style="background:#262c39; border-radius:16px; padding:1.5rem; margin-bottom:1.5rem; color:#fff; text-align:center;">
        <div style="font-size:12px; color:rgba(255,255,255,0.5); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:4px;">Next game</div>
        <div style="font-size:22px; font-weight:600; margin-bottom:4px;">
            {{ \Carbon\Carbon::parse($nextGame->gameDate)->format('l j F Y') }}
        </div>
        <div style="font-size:14px; color:rgba(255,255,255,0.6);">
            Round {{ $nextGame->gameRound }} · {{ $nextGame->seasonName }}
        </div>
        <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid rgba(255,255,255,0.1); font-size:13px; color:rgba(255,255,255,0.5);">
            {{ $totalPlayers }} player{{ $totalPlayers != 1 ? 's' : '' }} registered so far
        </div>
    </div>

    {{-- Parent attendance form --}}
<form method="POST" action="/reg/{{ $member->memberCode }}">
    @csrf
    <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:1.5rem; margin-bottom:1rem;">
    <p style="font-size:15px; font-weight:600; color:#262c39; margin-bottom:1rem;">Are you coming?</p>
    
    @if($totalPlayers >= 18 && $registration?->registrationStatus != 1)
    <div style="background:#fff3f3; border:1px solid #e24b4a; border-radius:8px; padding:12px 16px; font-size:14px; color:#262c39; margin-bottom:1rem;">
        <i class="fa-solid fa-triangle-exclamation" style="color:#e24b4a;"></i>
        Sorry — this game is full with {{ $totalPlayers }} players registered.
    </div>
    <button type="submit" name="status" value="2"
        style="width:100%; padding:16px; border-radius:12px; border:2px solid {{ ($registration?->registrationStatus == 2) ? '#e24b4a' : '#e8e8e8' }}; background:{{ ($registration?->registrationStatus == 2) ? '#fff3f3' : '#fff' }}; cursor:pointer; font-size:15px; font-weight:600; color:#262c39;">
        <i class="fa-solid fa-circle-xmark" style="color:#e24b4a; display:block; font-size:24px; margin-bottom:6px;"></i>
        Can't make it
    </button>
    @else
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <button type="submit" name="status" value="1"
            style="padding:16px; border-radius:12px; border:2px solid {{ ($registration?->registrationStatus == 1) ? '#7bba56' : '#e8e8e8' }}; background:{{ ($registration?->registrationStatus == 1) ? '#f0fdf4' : '#fff' }}; cursor:pointer; font-size:15px; font-weight:600; color:#262c39;">
            <i class="fa-solid fa-circle-check" style="color:#7bba56; display:block; font-size:24px; margin-bottom:6px;"></i>
            Yes, I'm in!
        </button>
        <button type="submit" name="status" value="2"
            style="padding:16px; border-radius:12px; border:2px solid {{ ($registration?->registrationStatus == 2) ? '#e24b4a' : '#e8e8e8' }}; background:{{ ($registration?->registrationStatus == 2) ? '#fff3f3' : '#fff' }}; cursor:pointer; font-size:15px; font-weight:600; color:#262c39;">
            <i class="fa-solid fa-circle-xmark" style="color:#e24b4a; display:block; font-size:24px; margin-bottom:6px;"></i>
            Can't make it
        </button>
    </div>
    @endif
</div>
</form>

{{-- Child attendance form --}}
@if($child && $registration?->registrationStatus == 1)
<form method="POST" action="/reg/{{ $member->memberCode }}">
    @csrf
    <input type="hidden" name="childID" value="{{ $child->memberID }}">
    <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:1.5rem; margin-bottom:1rem;">
        <p style="font-size:15px; font-weight:600; color:#262c39; margin-bottom:4px;">Is {{ $child->memberNameFirst }} coming?</p>
        <p style="font-size:13px; color:#888; margin-bottom:1rem;">Your child can only attend if you're also attending.</p>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <button type="submit" name="childStatus" value="1"
                style="padding:16px; border-radius:12px; border:2px solid {{ ($childRegistration?->registrationStatus == 1) ? '#7bba56' : '#e8e8e8' }}; background:{{ ($childRegistration?->registrationStatus == 1) ? '#f0fdf4' : '#fff' }}; cursor:pointer; font-size:15px; font-weight:600; color:#262c39;">
                <i class="fa-solid fa-circle-check" style="color:#7bba56; display:block; font-size:24px; margin-bottom:6px;"></i>
                Yes!
            </button>
            <button type="submit" name="childStatus" value="2"
                style="padding:16px; border-radius:12px; border:2px solid {{ ($childRegistration?->registrationStatus == 2) ? '#e24b4a' : '#e8e8e8' }}; background:{{ ($childRegistration?->registrationStatus == 2) ? '#fff3f3' : '#fff' }}; cursor:pointer; font-size:15px; font-weight:600; color:#262c39;">
                <i class="fa-solid fa-circle-xmark" style="color:#e24b4a; display:block; font-size:24px; margin-bottom:6px;"></i>
                Not this time
            </button>
        </div>
    </div>
</form>
@endif

    {{-- Account balance --}}
    <div style="background:#f8f8f8; border-radius:16px; padding:1.5rem; display:flex; align-items:center; justify-content:space-between;">
        <div>
            <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:4px;">Account balance</div>
            <div style="font-size:28px; font-weight:700; color:{{ $balance < 0 ? '#e24b4a' : '#262c39' }};">
                ${{ number_format(abs($balance), 2) }}{{ $balance < 0 ? ' owing' : '' }}
            </div>
        </div>
        <a href="/p/{{ $member->memberCode }}" class="btn btn-primary">
            <i class="fa-solid fa-credit-card"></i> Top up
        </a>
    </div>

</div>

@endsection