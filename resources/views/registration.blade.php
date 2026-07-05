@extends('layouts.app')

@section('title', 'Soccer Dads — Game Registration')

@section('content')

@php
    // Widen the container when the member has more than one night so the
    // self-contained cards can sit side by side; otherwise keep the narrow layout.
    $multi = $blocks->count() > 1;
@endphp

<style>
    .reg-nights {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        align-items: start;
    }
    @media (min-width: 768px) {
        .reg-nights.reg-nights--multi { grid-template-columns: 1fr 1fr; }
    }
</style>

{{-- Site gradient header (same bar as the Seasons page), member name as title --}}
<x-page-header :title="$member->memberNameFirst . '!'" />

{{-- Match the Seasons page: bottom PADDING on the content wrapper (not a margin,
     which collapses) gives a reliable gap above the shared site footer. --}}
<div style="padding:2.5rem 1.5rem 4rem;">
<div style="max-width:{{ $multi ? '960px' : '560px' }}; margin:0 auto;">

    <p style="text-align:center; font-size:14px; color:#888; margin-bottom:2rem;">Game registration</p>

    @if(session('success'))
    <div style="background:#f0fdf4; border:1px solid #7bba56; border-radius:8px; padding:12px 16px; margin-bottom:1.5rem; font-size:14px; color:#262c39;">
        <i class="fa-solid fa-circle-check" style="color:#7bba56;"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('bench'))
    <div style="background:#fff8ee; border:1px solid #e68a46; border-radius:8px; padding:12px 16px; margin-bottom:1.5rem; font-size:14px; color:#262c39;">
        <i class="fa-solid fa-clock" style="color:#e68a46;"></i> {{ session('bench') }}
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
        {{-- Top up button hidden until payment system verified --}}
    </div>
    @endif

    {{-- One self-contained card per accessible night with an upcoming game --}}
    @if($blocks->isNotEmpty())
    <div class="reg-nights {{ $multi ? 'reg-nights--multi' : '' }}">
        @foreach($blocks as $block)
            @include('partials.reg-night', [
                'block'  => $block,
                'member' => $member,
                'multi'  => $multi,
            ])
        @endforeach
    </div>
    @else
    {{-- Empty state: no accessible night has an upcoming game, OR the member has
         no night access at all. Same calm message either way — never an error. --}}
    <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:2.5rem 1.5rem; margin-bottom:1.5rem; text-align:center;">
        <div style="font-size:32px; margin-bottom:0.5rem;">⚽</div>
        <p style="font-size:16px; font-weight:600; color:#262c39; margin-bottom:0.25rem;">No upcoming games right now</p>
        <p style="font-size:14px; color:#888;">Check back soon — we'll show your next game here as soon as it's scheduled.</p>
    </div>
    @endif

    {{-- Account balance (per member — single figure below the sections) --}}
    <div style="background:#f8f8f8; border-radius:16px; padding:1.5rem; display:flex; align-items:center; justify-content:space-between;">
        <div>
            <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:4px;">Account balance</div>
            <div style="font-size:28px; font-weight:700; color:{{ $balance < 0 ? '#e24b4a' : '#262c39' }};">
                ${{ number_format(abs($balance), 2) }}{{ $balance < 0 ? ' owing' : '' }}
            </div>
        </div>
        <a href="/topup/{{ $member->memberCode }}" style="display:inline-flex; align-items:center; gap:8px; background:#262c39; color:#fff; padding:12px 24px; border-radius:10px; text-decoration:none; font-size:15px; font-weight:600;">
            <i class="fa-solid fa-credit-card"></i> Top up account
        </a>
    </div>

</div>
</div>

@endsection
