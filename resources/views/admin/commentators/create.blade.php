@extends('admin.layout')
@section('title', 'Add Commentator')
@section('content')

<div class="admin-card" style="max-width:700px;">
    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
        <a href="/admin/commentators" class="btn btn-secondary" style="padding:6px 12px;">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 style="margin-bottom:0;">Add Commentator</h2>
    </div>

    @if($errors->any())
    <div class="alert alert-error">
        <ul style="margin:0; padding-left:1rem;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="/admin/commentators/create">
        @csrf

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
                <label class="form-label">First Name</label>
                <input type="text" name="commentatorNameFirst" class="form-control" value="{{ old('commentatorNameFirst') }}" maxlength="32" required>
            </div>
            <div class="form-group">
                <label class="form-label">Last Name</label>
                <input type="text" name="commentatorNameLast" class="form-control" value="{{ old('commentatorNameLast') }}" maxlength="32" required>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:80px 1fr; gap:1rem;">
            <div class="form-group">
                <label class="form-label">Age</label>
                <input type="text" name="commentatorAge" class="form-control" value="{{ old('commentatorAge') }}" maxlength="2">
            </div>
            <div class="form-group">
                <label class="form-label">ElevenLabs Voice ID</label>
                <input type="text" name="commentatorElevenLabsID" class="form-control" value="{{ old('commentatorElevenLabsID') }}" maxlength="32">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Accent / Voice</label>
            <textarea name="commentatorAccent" class="form-control" rows="3">{{ old('commentatorAccent') }}</textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Background &amp; Personality</label>
            <textarea name="commentatorBackground" class="form-control" rows="5">{{ old('commentatorBackground') }}</textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Commentary Style</label>
            <textarea name="commentatorStyle" class="form-control" rows="5">{{ old('commentatorStyle') }}</textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Fun Facts / Catchphrases</label>
            <textarea name="commentatorFacts" class="form-control" rows="5">{{ old('commentatorFacts') }}</textarea>
        </div>

        <div style="display:flex; gap:2rem; margin-bottom:1.25rem;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px;">
                <input type="hidden" name="commentatorActive" value="0">
                <input type="checkbox" name="commentatorActive" value="1" {{ old('commentatorActive', '1') ? 'checked' : '' }} style="width:16px; height:16px;">
                <span>Active</span>
            </label>
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px;">
                <input type="hidden" name="commentatorVisible" value="0">
                <input type="checkbox" name="commentatorVisible" value="1" {{ old('commentatorVisible', '1') ? 'checked' : '' }} style="width:16px; height:16px;">
                <span>Visible</span>
            </label>
        </div>

        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Create Commentator
            </button>
            <a href="/admin/commentators" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection
