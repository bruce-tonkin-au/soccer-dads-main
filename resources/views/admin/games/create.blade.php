@extends('admin.layout')
@section('title', 'Add Game')
@section('content')

<div class="admin-card" style="max-width:500px;">
    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
        <a href="/admin/seasons/{{ $season->seasonID }}/games" class="btn btn-secondary" style="padding:6px 12px;">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 style="margin-bottom:0;">Add game — {{ $season->seasonName }}</h2>
    </div>
    <form method="POST" action="/admin/seasons/{{ $season->seasonID }}/games/create">
        @csrf
        <div class="form-group">
            <label class="form-label">Round number</label>
            <input type="number" name="gameRound" class="form-control" required>
        </div>
        <div class="form-group">
            <label class="form-label">Date</label>
            <input type="date" name="gameDate" class="form-control" required>
        </div>
        <div class="form-group">
            <label class="form-label">YouTube URL (optional)</label>
            <input type="text" name="gameYouTube" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
        </div>
        <div class="form-group">
            <label class="form-label">Visible</label>
            <select name="gameVisible" class="form-control">
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Create game
        </button>
    </form>
</div>

@endsection
