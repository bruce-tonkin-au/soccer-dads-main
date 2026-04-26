@extends('admin.layout')
@section('title', 'Messages')
@section('content')

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 style="margin-bottom:0;">Messages</h2>
        <a href="/admin/messages/create" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> New message
        </a>
    </div>
    @if($messages->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Subject</th>
                <th>Code</th>
                <th>Created</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($messages as $message)
            <tr>
                <td style="font-weight:500;">{{ $message->messageSubject }}</td>
                <td><code style="background:#f4f4f4; padding:2px 8px; border-radius:4px; font-size:13px;">{{ $message->messageCode }}</code></td>
                <td style="color:#888; font-size:13px;">{{ \Carbon\Carbon::parse($message->created_at)->format('j M Y') }}</td>
                <td>
                    @if($message->messageActive)
                    <span style="background:#f0fdf4; color:#7bba56; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">Active</span>
                    @else
                    <span style="background:#f4f4f4; color:#aaa; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">Inactive</span>
                    @endif
                </td>
                <td style="display:flex; gap:8px;">
                    <a href="/admin/messages/{{ $message->messageCode }}/links" class="btn btn-primary" style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-link"></i> Links
                    </a>
                    <a href="/admin/messages/{{ $message->messageCode }}/edit" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-pen"></i> Edit
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align:center; padding:3rem; color:#aaa;">
        <i class="fa-solid fa-message" style="font-size:48px; margin-bottom:1rem; display:block;"></i>
        No messages yet. Create your first one!
    </div>
    @endif
</div>

@endsection
