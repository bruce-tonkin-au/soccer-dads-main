@extends('admin.layout')
@section('title', 'Seasons')
@section('content')

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 style="margin-bottom:0;">Seasons</h2>
        <a href="/admin/seasons/create" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add season
        </a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Season</th>
                <th>Link</th>
                <th>Visible</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($seasons as $season)
            <tr>
                <td style="font-weight:500;">{{ $season->seasonName }}</td>
                <td><code style="background:#f4f4f4; padding:2px 8px; border-radius:4px; font-size:13px;">{{ $season->seasonLink }}</code></td>
                <td>
                    @if($season->seasonVisible)
                    <span style="background:#f0fdf4; color:#7bba56; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">Visible</span>
                    @else
                    <span style="background:#f4f4f4; color:#aaa; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">Hidden</span>
                    @endif
                </td>
                <td style="display:flex; gap:8px;">
                    <a href="/admin/seasons/{{ $season->seasonKey }}/games" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-calendar"></i> Games
                    </a>
                    <a href="/admin/seasons/{{ $season->seasonKey }}/edit" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-pen"></i> Edit
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
