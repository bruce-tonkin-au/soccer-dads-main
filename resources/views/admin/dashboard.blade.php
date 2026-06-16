@extends('admin.layout')
@section('title', 'Dashboard')
@section('content')

<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:2rem;">
    <div class="admin-card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:36px; font-weight:700; color:#458bc8;">{{ $stats['players'] }}</div>
        <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Players</div>
    </div>
    <div class="admin-card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:36px; font-weight:700; color:#7bba56;">{{ $stats['seasons'] }}</div>
        <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Seasons</div>
    </div>
    <div class="admin-card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:36px; font-weight:700; color:#e68a46;">{{ $stats['games'] }}</div>
        <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Game nights</div>
    </div>
    <div class="admin-card" style="text-align:center; margin-bottom:0;">
        <div style="font-size:36px; font-weight:700; color:#262c39;">{{ $stats['goals'] }}</div>
        <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-top:4px;">Goals scored</div>
    </div>
</div>


@if($nextGame)
<div class="admin-card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
        <h2 style="margin-bottom:0;">Next game — {{ \Carbon\Carbon::parse($nextGame->gameDate)->format('l j F Y') }} – Game ID: {{ $nextGame->gameID }}</h2>
        <div style="display:flex; gap:8px;">
            <a href="/admin/teams/{{ $nextGame->gameID }}" class="btn btn-primary">
                <i class="fa-solid fa-users"></i> Manage teams
            </a>
            <a href="/admin/registrations/{{ $nextGame->gameID }}" class="btn btn-secondary">
                <i class="fa-solid fa-list-check"></i> Registrations
            </a>
            <a href="/admin/print/{{ $nextGame->gameID }}" target="_blank" class="btn btn-secondary">
                <i class="fa-solid fa-print"></i> Print sheet
            </a>
            <a href="/admin/seasons/{{ $nextGame->gameSeasonID }}/games/{{ $nextGame->gameID }}/edit" class="btn btn-secondary">
                <i class="fa-solid fa-pen"></i> Edit game
            </a>
        </div>
    </div>
    <p id="registered-label" style="font-size:12px; color:#aaa; text-transform:uppercase; letter-spacing:0.07em; margin-bottom:8px;{{ (!$registrations || $registrations->count() === 0) ? ' display:none;' : '' }}">
        <i class="fa-solid fa-circle-check" style="margin-right:4px;"></i>Registered — <span id="registered-count">{{ $registrations ? $registrations->count() : 0 }}</span>/{{ $stats['players'] }}
    </p>
    <div style="display:flex; flex-wrap:wrap; gap:8px;" id="registered-list">
        @foreach($registrations ?? [] as $r)
        <div style="display:inline-flex; align-items:center; gap:6px; background:#f4f4f4; border-radius:20px; padding:4px 4px 4px 14px; font-size:13px; color:#262c39;" data-player-chip>
            <a href="/admin/players/{{ $r->memberID }}/edit" style="color:#262c39; text-decoration:none;">{{ $r->memberNameFirst }} {{ $r->memberNameLast }}</a>
            <form method="POST" action="/admin/registrations/{{ $nextGame->gameID }}/demote/{{ $r->memberID }}" style="margin:0;"
                  class="js-demote-form"
                  data-game-id="{{ $nextGame->gameID }}"
                  data-member-id="{{ $r->memberID }}"
                  data-player-name="{{ $r->memberNameFirst }} {{ $r->memberNameLast }}">
                @csrf
                <button type="submit" title="Mark as not going" style="background:none; border:none; cursor:pointer; color:#aaa; padding:4px 8px; font-size:12px; border-radius:12px;" onmouseover="this.style.color='#e24b4a'" onmouseout="this.style.color='#aaa'">
                    <i class="fa-solid fa-arrow-down"></i>
                </button>
            </form>
        </div>
        @endforeach
    </div>

    @if($benchRegistrations && $benchRegistrations->count() > 0)
    <div style="margin-top:1rem;" id="bench-section">
        <p style="font-size:12px; color:#aaa; text-transform:uppercase; letter-spacing:0.07em; margin-bottom:8px;">
            <i class="fa-solid fa-chair" style="margin-right:4px;"></i>Bench
        </p>
        <div style="display:flex; flex-wrap:wrap; gap:8px;" id="bench-list">
            @foreach($benchRegistrations as $i => $r)
            <div style="display:inline-flex; align-items:center; gap:6px; background:#fff8ec; border:1px solid #e68a46; border-radius:20px; padding:4px 14px; font-size:13px; color:#a0620a;">
                <span style="font-weight:700;">B{{ $i + 1 }}</span>
                <a href="/admin/players/{{ $r->memberID }}/edit" style="color:#a0620a; text-decoration:none;">{{ $r->memberNameFirst }} {{ $r->memberNameLast }}</a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($recentUnregistered && $recentUnregistered->count() > 0)
    <div style="margin-top:1rem;" id="not-yet-registered-section" data-player-section>
        <p style="font-size:12px; color:#aaa; text-transform:uppercase; letter-spacing:0.07em; margin-bottom:8px;">Not yet registered — played recently</p>
        <div style="display:flex; flex-wrap:wrap; gap:8px;" id="not-yet-registered-list">
            @foreach($recentUnregistered as $r)
            <div style="display:inline-flex; align-items:center; gap:2px; background:transparent; border:1.5px dashed #ccc; border-radius:20px; padding:4px 4px 4px 13px; font-size:13px; color:#aaa;" data-player-chip>
                <a href="/admin/players/{{ $r->memberID }}/edit" style="color:#aaa; text-decoration:none;">{{ $r->memberNameFirst }} {{ $r->memberNameLast }}</a>
                <form method="POST" action="/admin/registrations/{{ $nextGame->gameID }}/register/{{ $r->memberID }}" style="margin:0;"
                      class="js-register-form"
                      data-game-id="{{ $nextGame->gameID }}"
                      data-member-id="{{ $r->memberID }}"
                      data-player-name="{{ $r->memberNameFirst }} {{ $r->memberNameLast }}">
                    @csrf
                    <button type="submit" title="Register for game" style="background:none; border:none; cursor:pointer; color:#ccc; padding:4px 8px; font-size:12px; border-radius:12px;" onmouseover="this.style.color='#7bba56'" onmouseout="this.style.color='#ccc'">
                        <i class="fa-solid fa-arrow-up"></i>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div style="margin-top:1rem; text-align:right;">
        <form method="POST" action="/admin/games/{{ $nextGame->gameID }}/reset-night" style="display:inline;" onsubmit="return confirm('Reset the start time for this game night?')">
            @csrf
            <button type="submit" style="background:none; border:none; padding:0; font-size:11px; color:#ccc; cursor:pointer; text-decoration:underline;" onmouseover="this.style.color='#e24b4a'" onmouseout="this.style.color='#ccc'">reset night</button>
        </form>
    </div>

    <div style="margin-top:1rem;{{ (!$notGoingRegistrations || $notGoingRegistrations->count() === 0) ? ' display:none;' : '' }}" id="not-going-section" data-player-section>
        <p style="font-size:12px; color:#aaa; text-transform:uppercase; letter-spacing:0.07em; margin-bottom:8px;">
            <i class="fa-solid fa-circle-xmark" style="margin-right:4px;"></i>Not going
        </p>
        <div style="display:flex; flex-wrap:wrap; gap:8px;" id="not-going-list">
            @foreach($notGoingRegistrations ?? [] as $r)
            <div style="display:inline-flex; align-items:center; gap:2px; background:#fff3f3; border:1.5px dashed #e24b4a; border-radius:20px; padding:4px 4px 4px 13px; font-size:13px; color:#e24b4a;" data-player-chip>
                <a href="/admin/players/{{ $r->memberID }}/edit" style="color:#e24b4a; text-decoration:none;">{{ $r->memberNameFirst }} {{ $r->memberNameLast }}</a>
                <form method="POST" action="/admin/registrations/{{ $nextGame->gameID }}/register/{{ $r->memberID }}" style="margin:0;"
                      class="js-register-form"
                      data-game-id="{{ $nextGame->gameID }}"
                      data-member-id="{{ $r->memberID }}"
                      data-player-name="{{ $r->memberNameFirst }} {{ $r->memberNameLast }}">
                    @csrf
                    <button type="submit" title="Register for game" style="background:none; border:none; cursor:pointer; color:#e24b4a; padding:4px 8px; font-size:12px; border-radius:12px;" onmouseover="this.style.color='#7bba56'" onmouseout="this.style.color='#e24b4a'">
                        <i class="fa-solid fa-arrow-up"></i>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
    <div class="admin-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h2 style="margin-bottom:0;">Quick links</h2>
        </div>
        <div style="display:flex; flex-direction:column; gap:8px;">
            <a href="/admin/players/create" class="btn btn-primary" style="justify-content:flex-start;">
                <i class="fa-solid fa-user-plus"></i> Add new player
            </a>
            <a href="/admin/players" class="btn btn-secondary" style="justify-content:flex-start;">
                <i class="fa-solid fa-users"></i> Manage players
            </a>
            <a href="/admin/seasons" class="btn btn-secondary" style="justify-content:flex-start;">
                <i class="fa-solid fa-calendar"></i> Manage seasons
            </a>
        </div>
    </div>
    <div class="admin-card">
        <h2>Scoreboard</h2>
        <div style="font-size:14px; color:#888; line-height:2;">
            <div><a href="https://scoreboard.soccerdads.com.au" target="_blank" style="color:#458bc8;">scoreboard.soccerdads.com.au</a></div>
            <div style="margin-top:6px; line-height:1.5;">To add to your iPhone home screen: open the link in Safari, tap the Share button, then tap Add to Home Screen.</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function postAction(url) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: '_token=' + encodeURIComponent(csrfToken),
        }).then(function (r) { return r.json(); });
    }

    function insertSorted(container, chip, playerName) {
        var lastName = playerName.split(' ').slice(-1)[0].toLowerCase();
        var chips = Array.from(container.querySelectorAll('[data-player-chip]'));
        var inserted = false;
        for (var i = 0; i < chips.length; i++) {
            var chipLast = chips[i].querySelector('a').textContent.trim().split(' ').slice(-1)[0].toLowerCase();
            if (lastName < chipLast) {
                container.insertBefore(chip, chips[i]);
                inserted = true;
                break;
            }
        }
        if (!inserted) container.appendChild(chip);
    }

    function updateRegisteredCount() {
        var list = document.getElementById('registered-list');
        var countEl = document.getElementById('registered-count');
        if (list && countEl) countEl.textContent = list.querySelectorAll('[data-player-chip]').length;
    }

    function makeRegisteredChip(gameID, memberID, playerName) {
        var div = document.createElement('div');
        div.style.cssText = 'display:inline-flex; align-items:center; gap:6px; background:#f4f4f4; border-radius:20px; padding:4px 4px 4px 14px; font-size:13px; color:#262c39;';
        div.setAttribute('data-player-chip', '');
        div.innerHTML =
            '<a href="/admin/players/' + memberID + '/edit" style="color:#262c39; text-decoration:none;">' + playerName + '</a>' +
            '<form method="POST" action="/admin/registrations/' + gameID + '/demote/' + memberID + '" style="margin:0;"' +
            ' class="js-demote-form" data-game-id="' + gameID + '" data-member-id="' + memberID + '" data-player-name="' + playerName + '">' +
            '<input type="hidden" name="_token" value="' + csrfToken + '">' +
            '<button type="submit" title="Mark as not going" style="background:none; border:none; cursor:pointer; color:#aaa; padding:4px 8px; font-size:12px; border-radius:12px;"' +
            ' onmouseover="this.style.color=\'#e24b4a\'" onmouseout="this.style.color=\'#aaa\'">' +
            '<i class="fa-solid fa-arrow-down"></i></button></form>';
        return div;
    }

    function makeNotGoingChip(gameID, memberID, playerName) {
        var div = document.createElement('div');
        div.style.cssText = 'display:inline-flex; align-items:center; gap:2px; background:#fff3f3; border:1.5px dashed #e24b4a; border-radius:20px; padding:4px 4px 4px 13px; font-size:13px; color:#e24b4a;';
        div.setAttribute('data-player-chip', '');
        div.innerHTML =
            '<a href="/admin/players/' + memberID + '/edit" style="color:#e24b4a; text-decoration:none;">' + playerName + '</a>' +
            '<form method="POST" action="/admin/registrations/' + gameID + '/register/' + memberID + '" style="margin:0;"' +
            ' class="js-register-form" data-game-id="' + gameID + '" data-member-id="' + memberID + '" data-player-name="' + playerName + '">' +
            '<input type="hidden" name="_token" value="' + csrfToken + '">' +
            '<button type="submit" title="Register for game" style="background:none; border:none; cursor:pointer; color:#e24b4a; padding:4px 8px; font-size:12px; border-radius:12px;"' +
            ' onmouseover="this.style.color=\'#7bba56\'" onmouseout="this.style.color=\'#e24b4a\'">' +
            '<i class="fa-solid fa-arrow-up"></i></button></form>';
        return div;
    }

    // Event delegation — catches both server-rendered and JS-created chips
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.classList.contains('js-register-form') && !form.classList.contains('js-demote-form')) return;
        e.preventDefault();

        var gameID     = form.dataset.gameId;
        var memberID   = form.dataset.memberId;
        var playerName = form.dataset.playerName;
        var sourceChip = form.closest('[data-player-chip]');

        postAction(form.action).then(function (data) {
            if (!data.success) return;

            // Remove chip from its source section
            var sourceSection = sourceChip.closest('[data-player-section]');
            sourceChip.remove();
            if (sourceSection && !sourceSection.querySelector('[data-player-chip]')) {
                sourceSection.style.display = 'none';
            }

            if (form.classList.contains('js-register-form')) {
                // ↑ Move to registered
                var registeredList = document.getElementById('registered-list');
                insertSorted(registeredList, makeRegisteredChip(gameID, memberID, playerName), playerName);
                var label = document.getElementById('registered-label');
                if (label) label.style.display = '';
                updateRegisteredCount();
            } else {
                // ↓ Move to not going
                var notGoingSection = document.getElementById('not-going-section');
                var notGoingList    = document.getElementById('not-going-list');
                insertSorted(notGoingList, makeNotGoingChip(gameID, memberID, playerName), playerName);
                if (notGoingSection) notGoingSection.style.display = '';
                updateRegisteredCount();
            }
        });
    });
}());
</script>
@endpush

@endsection
