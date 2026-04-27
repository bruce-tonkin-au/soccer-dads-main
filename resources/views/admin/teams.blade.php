@extends('admin.layout')
@section('title', 'Team Selection')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    .rating-badge {
        font-size: 12px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 20px;
        background: #f4f4f4;
        color: #262c39;
    }
    .bench-badge {
        display: inline-block;
        font-size: 10px;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 20px;
        background: #888;
        color: #fff;
        margin-left: 6px;
        vertical-align: middle;
    }
    tr.is-benched td { opacity: 0.4; }
    #teams-table_wrapper { overflow-x: auto; }
</style>
@endpush

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem;">
    <div>
        <a href="/admin" style="font-size:13px; color:#888; text-decoration:none;">← Dashboard</a>
        <h1 style="font-size:24px; font-weight:700; color:#262c39; margin-top:4px;">
            Team Selection — {{ $game->seasonName }} Round {{ $game->gameRound }}
        </h1>
        <p style="font-size:13px; color:#888; margin-top:2px;">
            {{ \Carbon\Carbon::parse($game->gameDate)->format('l j F Y') }} · {{ $registered->count() }} players registered
        </p>
    </div>
    <button onclick="document.getElementById('teams-form').submit()" class="btn btn-primary" style="padding:12px 24px;">
        <i class="fa-solid fa-floppy-disk"></i> Save teams
    </button>
</div>

<form method="POST" action="/admin/teams/{{ $game->gameID }}" id="teams-form">
    @csrf

    {{-- Role legend --}}
    <div style="display:flex; gap:1.5rem; justify-content:flex-end; margin-bottom:1rem; font-size:12px; color:#888;">
        <span><i class="fa-solid fa-fire" style="color:#e68a46;"></i> Striker</span>
        <span><i class="fa-solid fa-wand-magic-sparkles" style="color:#458bc8;"></i> Playmaker</span>
        <span><i class="fa-solid fa-bolt" style="color:#f0c040;"></i> Workhorse</span>
        <span><i class="fa-solid fa-shield-halved" style="color:#7bba56;"></i> Defender</span>
    </div>

    {{-- Summary row --}}
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.5rem;">
        <div style="background:#458bc8; border-radius:10px; padding:1rem; text-align:center; color:#fff;">
            <div style="font-size:24px; font-weight:700;" id="count-3">0</div>
            <div style="font-size:13px; opacity:0.7; margin-top:4px;">avg <span id="avg-3">—</span></div>
            <div style="font-size:13px; opacity:0.7; margin-top:2px;">age <span id="age-3">—</span></div>
            <div style="font-size:12px; opacity:0.8; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Blue</div>
        </div>
        <div style="background:#7bba56; border-radius:10px; padding:1rem; text-align:center; color:#fff;">
            <div style="font-size:24px; font-weight:700;" id="count-2">0</div>
            <div style="font-size:13px; opacity:0.7; margin-top:4px;">avg <span id="avg-2">—</span></div>
            <div style="font-size:13px; opacity:0.7; margin-top:2px;">age <span id="age-2">—</span></div>
            <div style="font-size:12px; opacity:0.8; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Green</div>
        </div>
        <div style="background:#e68a46; border-radius:10px; padding:1rem; text-align:center; color:#fff;">
            <div style="font-size:24px; font-weight:700;" id="count-1">0</div>
            <div style="font-size:13px; opacity:0.7; margin-top:4px;">avg <span id="avg-1">—</span></div>
            <div style="font-size:13px; opacity:0.7; margin-top:2px;">age <span id="age-1">—</span></div>
            <div style="font-size:12px; opacity:0.8; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Orange</div>
        </div>
    </div>

    {{-- Player assignment table --}}
    <div class="admin-card">
        <table id="teams-table">
            <thead>
                <tr>
                    <th>Player</th>
                    <th>Rating</th>
                    <th style="text-align:center;">Peer reviews</th>
                    <th style="color:#458bc8; text-align:center;">Blue</th>
                    <th style="color:#7bba56; text-align:center;">Green</th>
                    <th style="color:#e68a46; text-align:center;">Orange</th>
                    <th style="text-align:center;">Unassigned</th>
                    <th style="text-align:center;">Bench</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registered as $player)
                <tr class="{{ $player->bench ? 'is-benched' : '' }}">
                    <td style="font-weight:500;">
                        {{ $player->memberNameFirst }} {{ $player->memberNameLast }}
                        <i class="fa-solid {{ $player->roleIcon }}" style="color:#aaa; font-size:11px; margin-left:6px;" title="{{ ucfirst($player->role) }}"></i>
                        <span class="bench-badge" style="{{ $player->bench ? '' : 'display:none;' }}">Bench</span>
                    </td>
                    <td><span class="rating-badge">{{ $player->rating }}</span></td>
                    <td style="text-align:center;">
                        @if($player->ratingCount >= 1)
                            <span style="background:#f0fdf4; color:#7bba56; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                                <i class="fa-solid fa-circle-check"></i> {{ $player->ratingCount }}
                            </span>
                        @else
                            <span style="background:#f4f4f4; color:#aaa; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                                <i class="fa-solid fa-circle-xmark"></i> None
                            </span>
                        @endif
                    </td>
                    @foreach([3 => 'Blue', 2 => 'Green', 1 => 'Orange'] as $teamID => $teamName)
                    <td style="text-align:center;">
                        <input type="radio"
                            name="teams[{{ $player->memberID }}]"
                            value="{{ $teamID }}"
                            {{ $player->teamID == $teamID ? 'checked' : '' }}
                            onchange="updateCounts()"
                            style="width:18px; height:18px; accent-color:{{ $teamColors[$teamID] }}; cursor:pointer;">
                    </td>
                    @endforeach
                    <td style="text-align:center;">
                        <input type="radio"
                            name="teams[{{ $player->memberID }}]"
                            value=""
                            {{ !$player->teamID ? 'checked' : '' }}
                            onchange="updateCounts()"
                            style="width:18px; height:18px; cursor:pointer;">
                    </td>
                    <td style="text-align:center;">
                        <button type="button"
                            class="btn btn-secondary bench-toggle"
                            style="padding:4px 10px; font-size:12px;{{ $player->bench ? ' background:#888; color:#fff; border-color:#888;' : '' }}"
                            data-member-id="{{ $player->memberID }}"
                            data-benched="{{ $player->bench ? '1' : '0' }}"
                            onclick="toggleBench(this)">
                            <span>{{ $player->bench ? 'Benched' : 'Bench' }}</span>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Auto-balance --}}
    <div style="text-align:center; margin-top:1rem;">
        <button type="button" onclick="autoBalance()" class="btn btn-secondary">
            <i class="fa-solid fa-shuffle"></i> Auto-balance teams
        </button>
        <p style="font-size:12px; color:#aaa; margin-top:8px;">Distributes players evenly by rating across all three teams.</p>
    </div>

</form>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    const gameID = {{ $game->gameID }};
    const csrfToken = '{{ csrf_token() }}';
    const playerData = @json($playerDataForJs);

    $(document).ready(function () {
        $('#teams-table').DataTable({
            paging: false,
            info: false,
            searching: false,
            columnDefs: [
                { orderable: false, targets: [3, 4, 5, 6, 7] }
            ],
            order: [[1, 'desc']]
        });
    });

    function updateCounts() {
        [1, 2, 3].forEach(teamID => {
            const checked = document.querySelectorAll(`input[type="radio"][value="${teamID}"]:checked`);
            document.getElementById(`count-${teamID}`).textContent = checked.length;

            let ratingTotal = 0;
            let ageTotal = 0, ageCount = 0;

            checked.forEach(input => {
                const memberID = input.name.match(/\[(\d+)\]/)[1];
                const entry = playerData.find(p => p.id == memberID);
                if (entry) {
                    ratingTotal += entry.rating;
                    if (entry.age !== null) { ageTotal += entry.age; ageCount++; }
                }
            });

            document.getElementById(`avg-${teamID}`).textContent =
                checked.length > 0 ? (ratingTotal / checked.length).toFixed(1) : '—';
            document.getElementById(`age-${teamID}`).textContent =
                ageCount > 0 ? (ageTotal / ageCount).toFixed(1) : '—';
        });
    }

    function toggleBench(btn) {
        const memberID = btn.dataset.memberId;

        fetch(`/admin/teams/${gameID}/bench/${memberID}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            const benched = data.bench === 1;
            btn.dataset.benched = benched ? '1' : '0';

            const row   = btn.closest('tr');
            const badge = row.querySelector('.bench-badge');
            const label = btn.querySelector('span');

            row.classList.toggle('is-benched', benched);
            badge.style.display = benched ? '' : 'none';
            label.textContent   = benched ? 'Benched' : 'Bench';
            btn.style.background  = benched ? '#888' : '';
            btn.style.color       = benched ? '#fff' : '';
            btn.style.borderColor = benched ? '#888' : '';

            const entry = playerData.find(p => p.id == memberID);
            if (entry) entry.bench = benched;
        });
    }

    function autoBalance() {
        const roleOrder = ['striker', 'playmaker', 'workhorse', 'defender'];
        const groups = {};
        roleOrder.forEach(r => groups[r] = []);
        playerData.forEach(p => {
            if (p.bench) return;
            const role = groups[p.role] ? p.role : 'workhorse';
            groups[role].push(p);
        });
        roleOrder.forEach(role => groups[role].sort((a, b) => b.rating - a.rating));

        const teamRatings = {1: 0, 2: 0, 3: 0};
        const teamOrder = [1, 2, 3].sort(() => Math.random() - 0.5);

        function weakestTeam() {
            let weakest = teamOrder[0];
            let lowestRating = Infinity;
            teamOrder.forEach(t => {
                if (teamRatings[t] < lowestRating) {
                    lowestRating = teamRatings[t];
                    weakest = t;
                }
            });
            return weakest;
        }

        function assign(player) {
            const t = weakestTeam();
            const radio = document.querySelector(`input[name="teams[${player.id}]"][value="${t}"]`);
            if (radio) radio.checked = true;
            teamRatings[t] += player.rating;
        }

        const teamCount = 3;
        roleOrder.forEach(role => groups[role].slice(0, teamCount).forEach(assign));
        roleOrder.forEach(role => groups[role].slice(teamCount).forEach(assign));

        updateCounts();
    }

    updateCounts();
</script>
@endpush

@endsection
