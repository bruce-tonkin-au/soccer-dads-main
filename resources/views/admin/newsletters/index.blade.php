@extends('admin.layout')
@section('title', 'Newsletters')
@section('content')

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 style="margin-bottom:0;">Newsletters</h2>
        <a href="/admin/newsletters/create" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> New Newsletter
        </a>
    </div>
    @if($newsletters->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Subject</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($newsletters as $newsletter)
            <tr>
                <td style="font-weight:500;">{{ $newsletter->subject }}</td>
                <td style="color:#888; font-size:13px;">{{ \Carbon\Carbon::parse($newsletter->created_at)->format('j M Y') }}</td>
                <td style="display:flex; gap:8px;">
                    <a href="/admin/newsletters/{{ $newsletter->newsletterID }}/edit" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-pen"></i> Edit
                    </a>
                    <form method="POST" action="/admin/newsletters/{{ $newsletter->newsletterID }}" onsubmit="return confirm('Delete this newsletter?');" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="padding:6px 12px; font-size:13px;">
                            <i class="fa-solid fa-trash"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align:center; padding:3rem; color:#aaa;">
        <i class="fa-solid fa-newspaper" style="font-size:48px; margin-bottom:1rem; display:block;"></i>
        No newsletters yet. Create your first one!
    </div>
    @endif
</div>

@endsection
