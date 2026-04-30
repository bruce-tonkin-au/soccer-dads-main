@extends('admin.layout')
@section('title', 'Players')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">
@endpush

@section('content')

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 style="margin-bottom:0;">Players ({{ $players->count() }})</h2>
        <a href="/admin/players/create" class="btn btn-primary">
            <i class="fa-solid fa-user-plus"></i> Add player
        </a>
    </div>
    <table id="players-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Code</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Balance</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($players as $player)
            <tr>
                <td style="font-weight:500;">{{ $player->memberNameFirst }} {{ $player->memberNameLast }}</td>
                <td><code style="background:#f4f4f4; padding:2px 8px; border-radius:4px; font-size:13px;">{{ $player->memberCode }}</code></td>
                <td style="color:#888; font-size:13px;">{{ $player->memberEmail ?: '—' }}</td>
                <td style="color:#888; font-size:13px;">{{ $player->memberPhoneMobile ?: '—' }}</td>
                <td style="font-weight:600; color:{{ $player->balance < 0 ? '#e24b4a' : ($player->balance > 0 ? '#7bba56' : '#888') }};">
                    ${{ number_format($player->balance, 2) }}
                </td>
                <td>
                    @if($player->memberActive)
                    <span style="background:#f0fdf4; color:#7bba56; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">Active</span>
                    @else
                    <span style="background:#f4f4f4; color:#aaa; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">Inactive</span>
                    @endif
                </td>
                <td>
                    <a href="/admin/players/{{ $player->memberID }}/edit" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-pen"></i> Edit
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new DataTable('#players-table', {
            pageLength: 25,
            columnDefs: [{ orderable: false, targets: -1 }]
        });
    });
</script>
@endpush
