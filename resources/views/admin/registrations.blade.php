@extends('admin.layout')
@section('title', 'Registrations')
@section('content')

@push('styles')
<style>
    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }
    .badge-active   { background: #f0fdf4; color: #2a7d2a; border: 1px solid #7bba56; }
    .badge-bench    { background: #fff8ec; color: #a0620a; border: 1px solid #e68a46; }
    .badge-notgoing { background: #fff3f3; color: #c0392b; border: 1px solid #e24b4a; }
    .event-type-label {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .event-registered    { background: #f0fdf4; color: #2a7d2a; }
    .event-deregistered  { background: #fff3f3; color: #c0392b; }
    .event-bench_added   { background: #fff8ec; color: #a0620a; }
    .event-bench_promoted { background: #e8f4ff; color: #1a5fa0; }
    .event-admin_registered { background: #f0fdf4; color: #2a7d2a; }
    .event-admin_deregistered { background: #fff3f3; color: #c0392b; }
    .game-selector {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 1.5rem;
    }
    .game-selector select {
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        padding: 8px 14px;
        font-size: 14px;
        color: #262c39;
        background: #fff;
        cursor: pointer;
        min-width: 320px;
    }
    .seq-num {
        font-weight: 700;
        color: #458bc8;
        min-width: 28px;
        display: inline-block;
    }
    .cap-bar {
        height: 6px;
        background: #e8e8e8;
        border-radius: 3px;
        margin: 6px 0 14px;
        overflow: hidden;
    }
    .cap-bar-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.3s ease;
    }
    .ts-small { font-size: 12px; color: #999; }
</style>
@endpush

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem;">
    <h1 style="font-size:22px; font-weight:700; color:#262c39;">Registrations</h1>
</div>

{{-- Game selector --}}
<div class="game-selector">
    <label style="font-size:13px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:0.08em;">Game</label>
    <select onchange="window.location='/admin/registrations/' + this.value">
        @foreach($allGames as $g)
        <option value="{{ $g->gameID }}" {{ $g->gameID == $game->gameID ? 'selected' : '' }}>
            {{ $g->seasonName }} — Round {{ $g->gameRound }}
            ({{ \Carbon\Carbon::parse($g->gameDate)->format('d M Y') }})
            @if($nextGame && $g->gameID == $nextGame->gameID) ★ Next game @endif
        </option>
        @endforeach
    </select>
</div>

{{-- Game header --}}
<div class="admin-card" style="margin-bottom:1.5rem;">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
        <div>
            <div style="font-size:18px; font-weight:700; color:#262c39;">
                {{ $game->seasonName }} — Round {{ $game->gameRound }}
            </div>
            <div style="font-size:14px; color:#888; margin-top:2px;">
                {{ \Carbon\Carbon::parse($game->gameDate)->format('l j F Y') }}
                &nbsp;·&nbsp; Game ID: {{ $game->gameID }}
            </div>
        </div>
        @php
            $activeCount = $registrations->where('registrationStatus', 1)->where('registrationBench', 0)->count();
            $benchCount  = $registrations->where('registrationStatus', 1)->where('registrationBench', 1)->count();
            $capFill     = min(100, round($activeCount / 18 * 100));
            $capColor    = $activeCount > 18 ? '#e24b4a' : ($activeCount >= 16 ? '#e68a46' : '#7bba56');
        @endphp
        <div style="text-align:right; min-width:160px;">
            <div style="font-size:28px; font-weight:700; color:{{ $capColor }};">
                {{ $activeCount }}<span style="font-size:16px; color:#aaa;">/18</span>
            </div>
            <div style="font-size:12px; color:#888;">active players</div>
            <div class="cap-bar">
                <div class="cap-bar-fill" style="width:{{ $capFill }}%; background:{{ $capColor }};"></div>
            </div>
            @if($benchCount > 0)
            <div style="font-size:12px; color:#e68a46;">+ {{ $benchCount }} on bench</div>
            @endif
            @if($activeCount > 18)
            <div style="font-size:12px; color:#e24b4a; font-weight:600; margin-top:4px;">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ $activeCount - 18 }} over cap
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Registrations table --}}
<div class="admin-card">
    <h2>Current registrations ({{ $registrations->count() }} total)</h2>

    @if($registrations->isEmpty())
    <p style="color:#888; font-size:14px;">No registrations recorded for this game.</p>
    @else
    <table>
        <thead>
            <tr>
                <th style="width:44px;">#</th>
                <th>Player</th>
                <th>First registered</th>
                <th>Last updated</th>
                <th>Status</th>
                <th style="width:96px;">Bench order</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registrations as $r)
            @php
                $regTime     = \Carbon\Carbon::parse($r->registrationCreated)->timezone('Australia/Adelaide');
                $editTime    = \Carbon\Carbon::parse($r->registrationEdited)->timezone('Australia/Adelaide');
                $wasEdited   = abs($regTime->diffInSeconds($editTime)) > 5;
            @endphp
            <tr>
                <td>
                    @if($r->activeSequence !== null)
                        <span class="seq-num">{{ $r->activeSequence }}</span>
                    @elseif($r->benchSequence !== null)
                        <span class="seq-num" style="color:#e68a46;">B{{ $r->benchSequence }}</span>
                    @else
                        <span style="color:#ccc;">—</span>
                    @endif
                </td>
                <td>
                    <a href="/admin/players/{{ $r->memberID }}/edit" style="color:#262c39; font-weight:500; text-decoration:none;">
                        {{ $r->memberNameFirst }} {{ $r->memberNameLast }}
                    </a>
                </td>
                <td>
                    <span title="{{ $regTime->format('Y-m-d H:i:s') }}">
                        {{ $regTime->format('D j M, g:ia') }}
                    </span>
                </td>
                <td>
                    @if($wasEdited)
                    <span class="ts-small" title="{{ $editTime->format('Y-m-d H:i:s') }}">
                        {{ $editTime->format('D j M, g:ia') }}
                    </span>
                    @else
                    <span class="ts-small" style="color:#ddd;">—</span>
                    @endif
                </td>
                <td>
                    @if($r->registrationStatus == 1 && $r->registrationBench == 0)
                        @if($r->activeSequence !== null && $r->activeSequence <= 18)
                        <span class="status-badge badge-active">
                            Player {{ $r->activeSequence }} of 18
                        </span>
                        @elseif($r->activeSequence !== null)
                        <span class="status-badge" style="background:#fff3f3; color:#c0392b; border:1px solid #e24b4a;">
                            Player {{ $r->activeSequence }} — OVER CAP
                        </span>
                        @else
                        <span class="status-badge badge-active">Active</span>
                        @endif
                    @elseif($r->registrationStatus == 1 && $r->registrationBench == 1)
                        <span class="status-badge badge-bench">Bench {{ $r->benchSequence }}</span>
                    @elseif($r->registrationStatus == 2)
                        <span class="status-badge badge-notgoing">Not going</span>
                    @else
                        <span class="status-badge" style="background:#f4f4f4; color:#888;">Unknown</span>
                    @endif
                </td>
                <td>
                    @if($r->registrationStatus == 1 && $r->registrationBench == 1)
                    @php
                        $isFirstBench = $r->benchSequence <= 1;
                        $isLastBench  = $r->benchSequence >= $benchCount;
                    @endphp
                    <div style="display:flex; gap:4px;">
                        <form method="POST" action="/admin/registrations/{{ $game->gameID }}/bench/{{ $r->memberID }}/move/up" style="margin:0;">
                            @csrf
                            <button type="submit" title="Move up" {{ $isFirstBench ? 'disabled' : '' }}
                                style="background:#fff8ec; border:1px solid #e68a46; border-radius:6px; color:#a0620a; padding:3px 9px; font-size:12px; cursor:{{ $isFirstBench ? 'default' : 'pointer' }}; {{ $isFirstBench ? 'opacity:0.3;' : '' }}">
                                <i class="fa-solid fa-arrow-up"></i>
                            </button>
                        </form>
                        <form method="POST" action="/admin/registrations/{{ $game->gameID }}/bench/{{ $r->memberID }}/move/down" style="margin:0;">
                            @csrf
                            <button type="submit" title="Move down" {{ $isLastBench ? 'disabled' : '' }}
                                style="background:#fff8ec; border:1px solid #e68a46; border-radius:6px; color:#a0620a; padding:3px 9px; font-size:12px; cursor:{{ $isLastBench ? 'default' : 'pointer' }}; {{ $isLastBench ? 'opacity:0.3;' : '' }}">
                                <i class="fa-solid fa-arrow-down"></i>
                            </button>
                        </form>
                    </div>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Not yet registered — played this season (priority pool for the final round) --}}
@if($unregistered->isNotEmpty())
<div class="admin-card" id="unregistered-card">
    <h2>Not yet registered — played this season (<span id="unregistered-count">{{ $unregistered->count() }}</span>)</h2>
    <p style="font-size:12px; color:#888; margin-bottom:12px;">
        Ordered by games played this season (priority for the final round), then surname.
    </p>
    <div style="display:flex; flex-wrap:wrap; gap:8px;" id="unregistered-list">
        @foreach($unregistered as $r)
        <div style="display:inline-flex; align-items:center; gap:6px; background:transparent; border:1.5px dashed #ccc; border-radius:20px; padding:4px 4px 4px 13px; font-size:13px; color:#888;" data-player-chip>
            <a href="/admin/players/{{ $r->memberID }}/edit" style="color:#888; text-decoration:none;">{{ $r->memberNameFirst }} {{ $r->memberNameLast }}</a>
            <span title="Games played this season" style="font-size:11px; font-weight:700; color:#bbb;">{{ $r->gamesPlayed }}</span>
            <form method="POST" action="/admin/registrations/{{ $game->gameID }}/register/{{ $r->memberID }}" style="margin:0;" class="js-register-form">
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

{{-- Registration event log --}}
<div class="admin-card">
    <h2>Registration event log</h2>

    @if($events->isEmpty())
    <p style="color:#aaa; font-size:14px;">
        No events recorded for this game.
        @if($isNextGame)
            Event logging is now active — all future registration changes will appear here.
        @else
            Event logging was introduced after this game was played.
        @endif
    </p>
    @else
    <table>
        <thead>
            <tr>
                <th>When</th>
                <th>Player</th>
                <th>Event</th>
                <th>Sequence</th>
            </tr>
        </thead>
        <tbody>
            @foreach($events as $e)
            @php
                $eventTime = \Carbon\Carbon::parse($e->created_at)->timezone('Australia/Adelaide');
                $typeClass = 'event-' . $e->eventType;
                $typeLabels = [
                    'registered'       => 'Registered',
                    'deregistered'     => 'Deregistered',
                    'bench_added'      => 'Added to bench',
                    'bench_promoted'   => 'Promoted from bench',
                    'admin_registered' => 'Admin: registered',
                    'admin_deregistered' => 'Admin: deregistered',
                ];
                $label = $typeLabels[$e->eventType] ?? $e->eventType;
            @endphp
            <tr>
                <td>
                    <span title="{{ $eventTime->format('Y-m-d H:i:s') }}">
                        {{ $eventTime->format('D j M, g:ia') }}
                    </span>
                </td>
                <td>
                    <a href="/admin/players/{{ $e->memberID }}/edit" style="color:#262c39; font-weight:500; text-decoration:none;">
                        {{ $e->memberNameFirst }} {{ $e->memberNameLast }}
                    </a>
                </td>
                <td>
                    <span class="event-type-label {{ $typeClass }}">{{ $label }}</span>
                </td>
                <td>
                    @if($e->registrationSequence !== null)
                        <span style="font-weight:600; color:#458bc8;">
                            Player {{ $e->registrationSequence }} of 18
                        </span>
                    @else
                        <span style="color:#ccc;">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
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

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.classList.contains('js-register-form')) return;
        e.preventDefault();

        var chip = form.closest('[data-player-chip]');
        postAction(form.action).then(function (data) {
            if (!data.success) return;
            if (chip) chip.remove();

            var list  = document.getElementById('unregistered-list');
            var count = document.getElementById('unregistered-count');
            var card  = document.getElementById('unregistered-card');
            var remaining = list ? list.querySelectorAll('[data-player-chip]').length : 0;
            if (count) count.textContent = remaining;
            if (card && remaining === 0) card.style.display = 'none';
        });
    });
}());
</script>
@endpush

@endsection
