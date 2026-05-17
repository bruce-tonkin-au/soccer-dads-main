@extends('admin.layout')
@section('title', 'Commentators')
@section('content')

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 style="margin-bottom:0;">Commentators</h2>
        <a href="/admin/commentators/create" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add Commentator
        </a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Age</th>
                <th>ElevenLabs Voice ID</th>
                <th>Default</th>
                <th>Active</th>
                <th>Visible</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($commentators as $commentator)
            <tr>
                <td style="font-weight:500;">{{ $commentator->commentatorNameFirst }} {{ $commentator->commentatorNameLast }}</td>
                <td>{{ $commentator->commentatorAge ?? '—' }}</td>
                <td>
                    @if($commentator->commentatorElevenLabsID)
                    <code style="background:#f4f4f4; padding:2px 8px; border-radius:4px; font-size:12px;">{{ Str::limit($commentator->commentatorElevenLabsID, 20) }}</code>
                    @else
                    <span style="color:#aaa;">—</span>
                    @endif
                </td>
                <td>
                    @if($commentator->commentatorDefault)
                    <span style="background:#fef9ec; color:#e68a46; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">Default</span>
                    @else
                    <span style="color:#ddd;">—</span>
                    @endif
                </td>
                <td>
                    @if($commentator->commentatorActive)
                    <span style="background:#f0fdf4; color:#7bba56; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">Yes</span>
                    @else
                    <span style="background:#f4f4f4; color:#aaa; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">No</span>
                    @endif
                </td>
                <td>
                    @if($commentator->commentatorVisible)
                    <span style="background:#f0fdf4; color:#7bba56; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">Yes</span>
                    @else
                    <span style="background:#f4f4f4; color:#aaa; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">No</span>
                    @endif
                </td>
                <td>
                    <a href="/admin/commentators/{{ $commentator->commentatorID }}/edit" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-pen"></i> Edit
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#aaa; padding:2rem;">No commentators yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
