@extends('admin.layout')
@section('title', 'Team Selection')

@push('styles')
<style>
    .rating-badge {
        font-size: 12px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 20px;
        background: #f4f4f4;
        color: #262c39;
    }
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
            <div style="font-size:12px; opacity:0.8; text-transform:uppercase; letter-spacing:0.08em;">Blue</div>
        </div>
        <div style="background:#7bba56; border-radius:10px; padding:1rem; text-align:center; color:#fff;">
            <div style="font-size:24px; font-weight:700;" id="count-2">0</div>
            <div style="font-size:12px; opacity:0.8; text-transform:uppercase; letter-spacing:0.08em;">Green</div>
        </div>
        <div style="background:#e68a46; border-radius:10px; padding:1rem; text-align:center; color:#fff;">
            <div style="font-size:24px; font-weight:700;" id="count-1">0</div>
            <div style="font-size:12px; opacity:0.8; text-transform:uppercase; letter-spacing:0.08em;">Orange</div>
        </div>
    </div>

    {{-- Player assignment table --}}
    <div class="admin-card">
        <table>
            <thead>
                <tr>
                    <th>Player</th>
                    <th>Rating</th>
                    <th style="text-align:center;">Peer reviews</th>
                    <th style="color:#458bc8; text-align:center;">Blue</th>
                    <th style="color:#7bba56; text-align:center;">Green</th>
                    <th style="color:#e68a46; text-align:center;">Orange</th>
                    <th style="text-align:center;">Unassigned</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registered as $player)
                <tr>
                    <td style="font-weight:500;">
                        {{ $player->memberNameFirst }} {{ $player->memberNameLast }}
                        <i class="fa-solid {{ $player->roleIcon }}" style="color:#aaa; font-size:11px; margin-left:6px;" title="{{ ucfirst($player->role) }}"></i>
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
<script>
    const playerData = @json($registered->map(fn($p) => ['id' => $p->memberID, 'rating' => $p->rating, 'role' => $p->role])->values());

    function updateCounts() {
        [1, 2, 3].forEach(teamID => {
            const count = document.querySelectorAll(`input[type="radio"][value="${teamID}"]:checked`).length;
            document.getElementById(`count-${teamID}`).textContent = count;
        });
    }

    function autoBalance() {
        const roleOrder = ['striker', 'defender', 'playmaker', 'workhorse'];
        const groups = {};
        roleOrder.forEach(r => groups[r] = []);
        playerData.forEach(p => {
            const role = groups[p.role] ? p.role : 'workhorse';
            groups[role].push(p);
        });
        roleOrder.forEach(role => groups[role].sort((a, b) => b.rating - a.rating));

        const teamRatings = {1: 0, 2: 0, 3: 0};
        const teamCounts = {1: 0, 2: 0, 3: 0};

        const teamOrder = [1, 2, 3].sort(() => Math.random() - 0.5);
        let startingTeamIndex = 0;

        function assignToWeakestTeam(player) {
            let weakest;
            let lowestRating = Infinity;

            if (teamRatings[1] === 0 && teamRatings[2] === 0 && teamRatings[3] === 0) {
                weakest = teamOrder[startingTeamIndex % 3];
                startingTeamIndex++;
            } else {
                weakest = teamOrder[0];
                teamOrder.forEach(t => {
                    if (teamRatings[t] < lowestRating) {
                        lowestRating = teamRatings[t];
                        weakest = t;
                    }
                });
            }

            const teamID = weakest;
            const radio = document.querySelector(`input[name="teams[${player.id}]"][value="${teamID}"]`);
            if (radio) radio.checked = true;
            teamRatings[teamID] += player.rating;
        }

        roleOrder.forEach(role => groups[role].forEach(p => assignToWeakestTeam(p)));
        updateCounts();
    }

    updateCounts();
</script>
@endpush

@endsection
