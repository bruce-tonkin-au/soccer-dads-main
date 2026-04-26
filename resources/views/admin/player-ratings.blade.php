@extends('admin.layout')
@section('title', $player->memberNameFirst . ' ' . $player->memberNameLast . ' — Ratings')
@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
    <a href="/admin/ratings" class="btn btn-secondary" style="padding:6px 12px;">
        <i class="fa-solid fa-chevron-left"></i>
    </a>
    <h1 style="font-size:24px; font-weight:700; color:#262c39;">
        {{ $player->memberNameFirst }} {{ $player->memberNameLast }} — Ratings
    </h1>
</div>

@if($averages && $averages->total > 0)
<div style="display:grid; grid-template-columns:repeat(5,1fr); gap:1rem; margin-bottom:2rem;">
    <div class="admin-card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:32px; font-weight:700; color:#7bba56;">{{ $averages->avgGoal }}</div>
        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Goal / 4</div>
    </div>
    <div class="admin-card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:32px; font-weight:700; color:#458bc8;">{{ $averages->avgPassing }}</div>
        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Passing / 4</div>
    </div>
    <div class="admin-card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:32px; font-weight:700; color:#e68a46;">{{ $averages->avgWork }}</div>
        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Work / 4</div>
    </div>
    <div class="admin-card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:32px; font-weight:700; color:#9b59b6;">{{ $averages->avgDefending }}</div>
        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Defending / 4</div>
    </div>
    <div class="admin-card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:32px; font-weight:700; color:#f0c040;">{{ $averages->avgOverall }}</div>
        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Overall / 4</div>
    </div>
</div>

<div class="admin-card">
    <h2 style="margin-bottom:1rem;">Individual ratings ({{ $averages->total }})</h2>
    <table>
        <thead>
            <tr>
                <th>Rated by</th>
                <th style="text-align:center;">Goal</th>
                <th style="text-align:center;">Passing</th>
                <th style="text-align:center;">Work</th>
                <th style="text-align:center;">Defending</th>
                <th style="text-align:center;">Overall</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ratings as $r)
            <tr>
                <td>{{ $r->memberNameFirst }} {{ $r->memberNameLast }}</td>
                <td style="text-align:center; font-weight:600;">{{ $r->ratingGoal }}</td>
                <td style="text-align:center; font-weight:600;">{{ $r->ratingPassing }}</td>
                <td style="text-align:center; font-weight:600;">{{ $r->ratingWork }}</td>
                <td style="text-align:center; font-weight:600;">{{ $r->ratingDefending }}</td>
                <td style="text-align:center; font-weight:600;">{{ $r->ratingOverall }}</td>
                <td style="color:#888; font-size:13px;">{{ \Carbon\Carbon::parse($r->created_at)->format('j M Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="admin-card" style="text-align:center; padding:3rem; color:#aaa;">
    <i class="fa-solid fa-star" style="font-size:48px; margin-bottom:1rem; display:block;"></i>
    No ratings yet for this player.
</div>
@endif

@endsection
