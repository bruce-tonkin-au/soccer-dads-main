@extends('admin.layout')
@section('title', 'Edit Game')
@section('content')

<div class="admin-card" style="max-width:500px;">
    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
        <a href="/admin/seasons/{{ $season->seasonID }}/games" class="btn btn-secondary" style="padding:6px 12px;">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 style="margin-bottom:0;">Edit Round {{ $game->gameRound }} — {{ $season->seasonName }}</h2>
    </div>
    <form method="POST" action="/admin/seasons/{{ $season->seasonID }}/games/{{ $game->gameID }}/edit">
        @csrf
        <div class="form-group">
            <label class="form-label">Round number</label>
            <input type="number" name="gameRound" class="form-control" value="{{ $game->gameRound }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Date</label>
            <input type="date" name="gameDate" class="form-control" value="{{ \Carbon\Carbon::parse($game->gameDate)->format('Y-m-d') }}">
        </div>
        <div class="form-group">
            <label class="form-label">YouTube URL</label>
            <input type="text" name="gameYouTube" class="form-control" value="{{ $game->gameYouTube }}">
        </div>
        <div class="form-group">
            <label class="form-label">YouTube start time</label>
            <input type="datetime-local" name="gameYouTubeStart" class="form-control" value="{{ $game->gameYouTubeStart ? \Carbon\Carbon::parse($game->gameYouTubeStart)->format('Y-m-d\TH:i') : '' }}">
        </div>
        <div class="form-group">
            <label class="form-label">Visible</label>
            <select name="gameVisible" class="form-control">
                <option value="1" {{ $game->gameVisible ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ !$game->gameVisible ? 'selected' : '' }}>No</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Save changes
        </button>
    </form>
</div>

@endsection
