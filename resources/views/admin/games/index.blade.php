@extends('admin.layout')
@section('title', 'Games')
@section('content')

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <div>
            <a href="/admin/seasons" style="font-size:13px; color:#888; text-decoration:none;">← Seasons</a>
            <h2 style="margin-bottom:0; margin-top:4px;">{{ $season->seasonName }} — Games</h2>
        </div>
        <a href="/admin/seasons/{{ $season->seasonID }}/games/create" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add game
        </a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Round</th>
                <th>Date</th>
                <th>YouTube</th>
                <th>Visible</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($games as $game)
            <tr>
                <td style="font-weight:600;">Round {{ $game->gameRound }}</td>
                <td>{{ $game->gameDate }}</td>
                <td>{{ $game->gameYouTube ? '✓' : '—' }}</td>
                <td>
                    @if($game->gameVisible)
                    <span style="background:#f0fdf4; color:#7bba56; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">Yes</span>
                    @else
                    <span style="background:#f4f4f4; color:#aaa; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">No</span>
                    @endif
                </td>
                <td style="display:flex; gap:8px;">
                    <a href="/admin/teams/{{ $game->gameID }}" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-users"></i> Teams
                    </a>
                    <a href="/admin/print/{{ $game->gameID }}" target="_blank" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-print"></i> Print
                    </a>
                    <a href="/admin/seasons/{{ $season->seasonID }}/games/{{ $game->gameID }}/edit" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-pen"></i> Edit
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
