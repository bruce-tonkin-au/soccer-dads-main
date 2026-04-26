@extends('admin.layout')
@section('title', 'Edit Season')
@section('content')

<div class="admin-card" style="max-width:500px;">
    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
        <a href="/admin/seasons" class="btn btn-secondary" style="padding:6px 12px;">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 style="margin-bottom:0;">Edit — {{ $season->seasonName }}</h2>
    </div>
    <form method="POST" action="/admin/seasons/{{ $season->seasonKey }}/edit">
        @csrf
        <div class="form-group">
            <label class="form-label">Season name</label>
            <input type="text" name="seasonName" class="form-control" value="{{ $season->seasonName }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Season link (URL code)</label>
            <input type="text" name="seasonLink" class="form-control" value="{{ $season->seasonLink }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Visible</label>
            <select name="seasonVisible" class="form-control">
                <option value="1" {{ $season->seasonVisible ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ !$season->seasonVisible ? 'selected' : '' }}>No</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Save changes
        </button>
    </form>
</div>

@endsection
