@extends('admin.layout')
@section('title', 'Players')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">
@endpush

@section('content')

<div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:1rem; margin-bottom:1.5rem;">
    <div class="admin-card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:28px; font-weight:700; color:#e24b4a;">${{ number_format(abs($totalOwing), 2) }}</div>
        <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Total owing</div>
    </div>
    <div class="admin-card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:28px; font-weight:700; color:#7bba56;">${{ number_format($totalOwed, 2) }}</div>
        <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Total owed</div>
    </div>
    <div class="admin-card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:28px; font-weight:700; color:#7bba56;">{{ $claimedCount }}</div>
        <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Claimed accounts</div>
    </div>
    <div class="admin-card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:28px; font-weight:700; color:#e68a46;">{{ $unclaimedCount }}</div>
        <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Unclaimed</div>
    </div>
</div>

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
                <th>Claimed</th>
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
                <td data-order="{{ $player->memberClaimed ? 1 : 0 }}">
                    @if($player->memberClaimed)
                    <span title="Claimed {{ $player->memberClaimedAt ? \Carbon\Carbon::parse($player->memberClaimedAt)->format('j M Y') : '' }}" style="background:#f0fdf4; color:#7bba56; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                        <i class="fa-solid fa-circle-check"></i> Yes
                    </span>
                    @else
                    <span style="background:#fff8ee; color:#e68a46; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                        <i class="fa-solid fa-clock"></i> No
                    </span>
                    @endif
                </td>
                <td style="white-space:nowrap;">
                    @unless($player->memberClaimed)
                    <button type="button"
                            class="btn btn-secondary copy-claim-link"
                            data-url="{{ url('/claim/' . $player->memberCode) }}"
                            style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-link"></i> Copy claim link
                    </button>
                    @endunless
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

        document.body.addEventListener('click', function (e) {
            const btn = e.target.closest('.copy-claim-link');
            if (!btn) return;
            const url = btn.dataset.url;
            const original = btn.innerHTML;
            const restore = () => { btn.innerHTML = original; };

            const onSuccess = () => {
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
                setTimeout(restore, 1500);
            };
            const onFail = () => {
                window.prompt('Copy this claim link:', url);
                restore();
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(onSuccess).catch(onFail);
            } else {
                onFail();
            }
        });
    });
</script>
@endpush
