@extends('admin.layout')
@section('title', 'Games')
@section('content')

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <div>
            <a href="/admin/seasons" style="font-size:13px; color:#888; text-decoration:none;">← Seasons</a>
            <h2 style="margin-bottom:0; margin-top:4px;">{{ $season->seasonName }} — Games</h2>
        </div>
        <a href="/admin/seasons/{{ $season->seasonID }}/games/create" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add game
        </a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Round</th>
                <th>Date</th>
                <th>YouTube</th>
                <th>Visible</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($games as $game)
            @php
                $isChargeable  = $game->gameDate >= '2026-05-01';
                $hasTeams      = $isChargeable && $gamesWithTeams->contains($game->gameID);
                $alreadyCharged = $isChargeable && $chargedGameIDs->contains($game->gameID);
            @endphp
            <tr>
                <td style="font-weight:600;">Round {{ $game->gameRound }}</td>
                <td>{{ $game->gameDate }}</td>
                <td>{{ $game->gameYouTube ? '✓' : '—' }}</td>
                <td>
                    @if($game->gameVisible)
                    <span style="background:#f0fdf4; color:#7bba56; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">Yes</span>
                    @else
                    <span style="background:#f4f4f4; color:#aaa; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">No</span>
                    @endif
                </td>
                <td style="display:flex; gap:8px; align-items:center;">
                    <a href="/admin/teams/{{ $game->gameID }}" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-users"></i> Teams
                    </a>
                    <a href="/admin/print/{{ $game->gameID }}" target="_blank" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-print"></i> Print
                    </a>
                    <a href="/admin/seasons/{{ $season->seasonID }}/games/{{ $game->gameID }}/edit" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-pen"></i> Edit
                    </a>
                    @if($alreadyCharged)
                    <span style="display:inline-flex; align-items:center; gap:4px; color:#7bba56; font-size:13px; font-weight:600; padding:6px 12px;">
                        <i class="fa-solid fa-circle-check"></i> Charged
                    </span>
                    @elseif($hasTeams)
                    <button
                        class="btn btn-secondary js-charge-btn"
                        style="padding:6px 12px; font-size:13px; color:#e68a46; border-color:#e68a46;"
                        data-game-id="{{ $game->gameID }}"
                        data-season-id="{{ $season->seasonID }}"
                        data-round="{{ $game->gameRound }}"
                        data-preview-url="/admin/seasons/{{ $season->seasonID }}/games/{{ $game->gameID }}/preview-charges"
                    >
                        <i class="fa-solid fa-dollar-sign"></i> Charge players
                    </button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Charge players modal --}}
<div id="charge-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; padding:2rem; max-width:540px; width:90%; max-height:80vh; display:flex; flex-direction:column; box-shadow:0 8px 32px rgba(0,0,0,0.18);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
            <h2 style="font-size:18px; font-weight:700; color:#262c39; margin:0;" id="modal-title">Charge players</h2>
            <button id="modal-close" style="background:none; border:none; font-size:20px; color:#aaa; cursor:pointer; padding:4px;">×</button>
        </div>
        <div id="modal-body" style="overflow-y:auto; flex:1;">
            <p style="color:#888; font-size:14px;">Loading…</p>
        </div>
        <div id="modal-footer" style="margin-top:1.25rem; display:flex; gap:8px; justify-content:flex-end;">
            <button id="modal-cancel" class="btn btn-secondary">Cancel</button>
            <form id="charge-form" method="POST" style="display:none;">
                @csrf
                <button type="submit" class="btn btn-primary" style="background:#e68a46;">
                    <i class="fa-solid fa-dollar-sign"></i> Confirm &amp; charge
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var modal       = document.getElementById('charge-modal');
    var modalTitle  = document.getElementById('modal-title');
    var modalBody   = document.getElementById('modal-body');
    var chargeForm  = document.getElementById('charge-form');
    var modalClose  = document.getElementById('modal-close');
    var modalCancel = document.getElementById('modal-cancel');

    function openModal() { modal.style.display = 'flex'; }
    function closeModal() { modal.style.display = 'none'; chargeForm.style.display = 'none'; }

    modalClose.addEventListener('click', closeModal);
    modalCancel.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });

    document.querySelectorAll('.js-charge-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var gameID     = btn.dataset.gameId;
            var seasonID   = btn.dataset.seasonId;
            var round      = btn.dataset.round;
            var previewUrl = btn.dataset.previewUrl;

            modalTitle.textContent = 'Charge players — Round ' + round;
            modalBody.innerHTML = '<p style="color:#888; font-size:14px;">Loading…</p>';
            chargeForm.style.display = 'none';
            openModal();

            fetch(previewUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.error) {
                    modalBody.innerHTML = '<p style="color:#e24b4a; font-size:14px;">' + data.error + '</p>';
                    return;
                }

                var charges = data.charges;
                if (!charges || charges.length === 0) {
                    modalBody.innerHTML = '<p style="color:#888; font-size:14px;">No players found in teams for this game night.</p>';
                    return;
                }

                var html = '<p style="font-size:13px; color:#888; margin-bottom:12px;">Review charges below, then confirm to apply them.</p>';
                html += '<table style="width:100%; border-collapse:collapse; font-size:14px;">';
                html += '<thead><tr>';
                html += '<th style="text-align:left; padding:6px 8px; background:#f8f8f8; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#262c39; border-bottom:1px solid #e8e8e8;">Player</th>';
                html += '<th style="text-align:right; padding:6px 8px; background:#f8f8f8; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#262c39; border-bottom:1px solid #e8e8e8;">Amount</th>';
                html += '<th style="text-align:left; padding:6px 8px; background:#f8f8f8; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#262c39; border-bottom:1px solid #e8e8e8;">Reason</th>';
                html += '</tr></thead><tbody>';

                charges.forEach(function (c) {
                    var amtDisplay = c.amount === 0 ? '$0.00' : '$' + Math.abs(c.amount).toFixed(2);
                    var amtColor   = c.amount === 0 ? '#aaa' : '#262c39';
                    html += '<tr>';
                    html += '<td style="padding:8px; border-bottom:1px solid #f0f0f0; color:#262c39;">' + c.memberName + '</td>';
                    html += '<td style="padding:8px; border-bottom:1px solid #f0f0f0; text-align:right; font-weight:600; color:' + amtColor + ';">' + amtDisplay + '</td>';
                    html += '<td style="padding:8px; border-bottom:1px solid #f0f0f0; color:#888; font-size:13px;">' + c.reason + '</td>';
                    html += '</tr>';
                });

                html += '</tbody></table>';
                modalBody.innerHTML = html;

                chargeForm.action = '/admin/seasons/' + seasonID + '/games/' + gameID + '/charge';
                chargeForm.style.display = 'block';
            })
            .catch(function () {
                modalBody.innerHTML = '<p style="color:#e24b4a; font-size:14px;">Failed to load preview. Please try again.</p>';
            });
        });
    });
}());
</script>
@endpush

@endsection
