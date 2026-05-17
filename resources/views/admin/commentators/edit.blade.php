@extends('admin.layout')
@section('title', 'Edit Commentator')
@section('content')

<div class="admin-card" style="max-width:700px;">
    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
        <a href="/admin/commentators" class="btn btn-secondary" style="padding:6px 12px;">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 style="margin-bottom:0;">Edit — {{ $commentator->commentatorNameFirst }} {{ $commentator->commentatorNameLast }}</h2>
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

    <form method="POST" action="/admin/commentators/{{ $commentator->commentatorID }}/edit">
        @csrf

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
                <label class="form-label">First Name</label>
                <input type="text" name="commentatorNameFirst" class="form-control" value="{{ old('commentatorNameFirst', $commentator->commentatorNameFirst) }}" maxlength="32" required>
            </div>
            <div class="form-group">
                <label class="form-label">Last Name</label>
                <input type="text" name="commentatorNameLast" class="form-control" value="{{ old('commentatorNameLast', $commentator->commentatorNameLast) }}" maxlength="32" required>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:80px 1fr; gap:1rem;">
            <div class="form-group">
                <label class="form-label">Age</label>
                <input type="text" name="commentatorAge" class="form-control" value="{{ old('commentatorAge', $commentator->commentatorAge) }}" maxlength="2">
            </div>
            <div class="form-group">
                <label class="form-label">ElevenLabs Voice ID</label>
                <input type="text" name="commentatorElevenLabsID" class="form-control" value="{{ old('commentatorElevenLabsID', $commentator->commentatorElevenLabsID) }}" maxlength="32">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Accent / Voice</label>
            <textarea name="commentatorAccent" class="form-control" rows="3">{{ old('commentatorAccent', $commentator->commentatorAccent) }}</textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Background &amp; Personality</label>
            <textarea name="commentatorBackground" class="form-control" rows="5">{{ old('commentatorBackground', $commentator->commentatorBackground) }}</textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Commentary Style</label>
            <textarea name="commentatorStyle" class="form-control" rows="5">{{ old('commentatorStyle', $commentator->commentatorStyle) }}</textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Fun Facts / Catchphrases</label>
            <textarea name="commentatorFacts" class="form-control" rows="5">{{ old('commentatorFacts', $commentator->commentatorFacts) }}</textarea>
        </div>

        <div style="display:flex; gap:2rem; margin-bottom:1.25rem;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px;">
                <input type="hidden" name="commentatorActive" value="0">
                <input type="checkbox" name="commentatorActive" value="1" {{ old('commentatorActive', $commentator->commentatorActive) ? 'checked' : '' }} style="width:16px; height:16px;">
                <span>Active</span>
            </label>
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px;">
                <input type="hidden" name="commentatorVisible" value="0">
                <input type="checkbox" name="commentatorVisible" value="1" {{ old('commentatorVisible', $commentator->commentatorVisible) ? 'checked' : '' }} style="width:16px; height:16px;">
                <span>Visible</span>
            </label>
        </div>

        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
            <a href="/admin/commentators" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    <div style="margin-top:1.5rem; padding-top:1.25rem; border-top:1px solid #f0f0f0; font-size:12px; color:#aaa;">
        Created: {{ $commentator->commentatorCreated }}
        &nbsp;&middot;&nbsp;
        Last edited: {{ $commentator->commentatorEdited }}
    </div>
</div>

@endsection
