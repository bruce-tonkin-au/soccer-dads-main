@extends('admin.layout')
@section('title', 'Player Ratings')
@section('content')

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h2 style="margin-bottom:0;">Player Ratings Summary</h2>
        <span style="font-size:13px; color:#888;">{{ $ratings->count() }} total ratings submitted</span>
    </div>

    @if($summary->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Player</th>
                <th style="text-align:center;">Ratings</th>
                <th style="text-align:center;">Goal</th>
                <th style="text-align:center;">Passing</th>
                <th style="text-align:center;">Work</th>
                <th style="text-align:center;">Defending</th>
                <th style="text-align:center;">Overall</th>
                <th style="text-align:center;">Composite</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary as $s)
            <tr>
                <td style="font-weight:500;">{{ $s->memberNameFirst }} {{ $s->memberNameLast }}</td>
                <td style="text-align:center; color:#888;">{{ $s->ratingCount }}</td>
                <td style="text-align:center;">
                    <span style="background:#f0fdf4; color:#7bba56; padding:2px 8px; border-radius:20px; font-size:12px; font-weight:600;">{{ $s->avgGoal }}/4</span>
                </td>
                <td style="text-align:center;">
                    <span style="background:#f0f7ff; color:#458bc8; padding:2px 8px; border-radius:20px; font-size:12px; font-weight:600;">{{ $s->avgPassing }}/4</span>
                </td>
                <td style="text-align:center;">
                    <span style="background:#fff3f0; color:#e68a46; padding:2px 8px; border-radius:20px; font-size:12px; font-weight:600;">{{ $s->avgWork }}/4</span>
                </td>
                <td style="text-align:center;">
                    <span style="background:#f8f0ff; color:#9b59b6; padding:2px 8px; border-radius:20px; font-size:12px; font-weight:600;">{{ $s->avgDefending }}/4</span>
                </td>
                <td style="text-align:center;">
                    <span style="background:#fffbf0; color:#f0c040; padding:2px 8px; border-radius:20px; font-size:12px; font-weight:600;">{{ $s->avgOverall }}/4</span>
                </td>
                <td style="text-align:center;">
                    <span style="font-size:18px; font-weight:700; color:#262c39;">{{ $s->compositeRating }}</span>
                </td>
                <td>
                    <a href="/admin/ratings/{{ $s->ratedMemberID }}" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-chart-bar"></i> Detail
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align:center; padding:3rem; color:#aaa;">
        <i class="fa-solid fa-star" style="font-size:48px; margin-bottom:1rem; display:block;"></i>
        No ratings submitted yet. Share the rating link with players!
    </div>
    @endif
</div>

@endsection
