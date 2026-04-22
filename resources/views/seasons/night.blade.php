@extends('layouts.app')

@section('title', 'Round ' . $game->gameRound . ' — ' . $season->seasonName . ' — Soccer Dads')

@section('content')

<div style="padding:4rem 2rem;">
    <div class="container">

        <a href="/seasons/{{ $season->seasonKey }}" style="font-size:13px; color:#888; text-decoration:none; display:inline-flex; align-items:center; gap:6px; margin-bottom:1.5rem;">
            <i class="fa-solid fa-chevron-left"></i> {{ $season->seasonName }}
        </a>

        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:64px; color:#262c39; margin-bottom:0.25rem;">
            Round {{ $game->gameRound }}
        </h1>
        <p style="font-size:15px; color:#888; margin-bottom:2rem;">
            {{ \Carbon\Carbon::parse($game->gameDate)->format('l j F Y') }}
        </p>

        {{-- YouTube embed --}}
        @if($youtubeID)
        <div style="margin-bottom:2rem; border-radius:16px; overflow:hidden; aspect-ratio:16/9;">
            <iframe
                width="100%"
                height="100%"
                src="https://www.youtube.com/embed/{{ $youtubeID }}"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen>
            </iframe>
        </div>
        @endif

        {{-- Results grid --}}
        <h2 style="font-size:18px; font-weight:600; color:#262c39; margin-bottom:1rem;">Results</h2>
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:2rem;">
            @foreach($results->groupBy('scoringRound') as $round => $games)
            @foreach($games as $game)
            <div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; padding:1rem; text-align:center;">
                <div style="font-size:11px; color:#aaa; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:8px;">Game {{ $game->scoringGame }}</div>
                <div style="display:flex; align-items:center; justify-content:center; gap:12px;">
                    <div style="text-align:center;">
                        <div style="width:12px; height:12px; border-radius:50%; background:{{ $game->homeTeam['color'] }}; margin:0 auto 4px;"></div>
                        <div style="font-size:24px; font-weight:700; color:#262c39;">{{ $game->homeGoals }}</div>
                    </div>
                    <div style="font-size:12px; color:#aaa;">—</div>
                    <div style="text-align:center;">
                        <div style="width:12px; height:12px; border-radius:50%; background:{{ $game->awayTeam['color'] }}; margin:0 auto 4px;"></div>
                        <div style="font-size:24px; font-weight:700; color:#262c39;">{{ $game->awayGoals }}</div>
                    </div>
                </div>
            </div>
            @endforeach
            @endforeach
        </div>

        {{-- Goals and assists --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:2rem; margin-bottom:2rem;">

            {{-- Goals --}}
            <div>
                <h2 style="font-size:18px; font-weight:600; color:#262c39; margin-bottom:1rem;">Goals</h2>
                <div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden;">
                    @php
                    $goalActions = $actions->where('actionGoal', 1)->where('memberID', '!=', null)->groupBy('memberID')->map(fn($g) => $g->first())->sortByDesc(fn($a) => $nightGoals[$a->memberID] ?? 0);
                    @endphp
                    @forelse($goalActions as $memberID => $action)
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 16px; border-bottom:1px solid #f0f0f0;">
                        <span style="font-size:14px; color:#262c39;">{{ $action->scorerFirst }} {{ $action->scorerLast }}</span>
                        <div style="display:flex; gap:12px; align-items:center;">
                            <span style="font-size:14px; font-weight:600; color:#262c39;">{{ $nightGoals[$memberID] ?? 0 }}</span>
                            <span style="font-size:12px; color:#aaa;">YTD: {{ $seasonGoals[$memberID] ?? 0 }}</span>
                        </div>
                    </div>
                    @empty
                    <div style="padding:16px; font-size:14px; color:#aaa; text-align:center;">No goals recorded</div>
                    @endforelse
                </div>
            </div>

            {{-- Assists --}}
            <div>
                <h2 style="font-size:18px; font-weight:600; color:#262c39; margin-bottom:1rem;">Assists</h2>
                <div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden;">
                    @php
                    $assistActions = $actions->where('actionGoal', 1)->whereNotNull('secondID')->groupBy('secondID')->map(fn($g) => $g->first())->sortByDesc(fn($a) => $seasonAssists[$a->secondID] ?? 0);
                    @endphp
                    @forelse($assistActions as $secondID => $action)
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 16px; border-bottom:1px solid #f0f0f0;">
                        <span style="font-size:14px; color:#262c39;">{{ $action->assisterFirst }} {{ $action->assisterLast }}</span>
                        <div style="display:flex; gap:12px; align-items:center;">
                            <span style="font-size:14px; font-weight:600; color:#262c39;">{{ $actions->where('actionGoal', 1)->where('secondID', $secondID)->count() }}</span>
                            <span style="font-size:12px; color:#aaa;">YTD: {{ $seasonAssists[$secondID] ?? 0 }}</span>
                        </div>
                    </div>
                    @empty
                    <div style="padding:16px; font-size:14px; color:#aaa; text-align:center;">No assists recorded</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- All actions --}}
<h2 style="font-size:18px; font-weight:600; color:#262c39; margin-bottom:1rem;">Events</h2>
<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden;">
    <table id="events-table" style="width:100%;">
        <thead>
            <tr>
                <th>Round</th>
                <th>Game</th>
                <th>Team</th>
                <th>Type</th>
                <th>Player</th>
                <th>Assist</th>
                @if($youtubeID && $youtubeStart)
                <th>Video</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($actions as $action)
            <tr>
                <td>{{ $action->scoringRound }}</td>
                <td>{{ $action->scoringGame }}</td>
                <td>
                    <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:{{ $teams[$action->teamID]['color'] ?? '#aaa' }};"></span>
                    {{ $teams[$action->teamID]['name'] ?? '' }}
                </td>
                <td>
                    @if($action->typeID == 1)
                        <i class="fa-solid fa-futbol" style="color:{{ $teams[$action->teamID]['color'] ?? '#aaa' }};"></i> Goal
                    @elseif($action->typeID == 2)
                        <i class="fa-solid fa-bullseye" style="color:{{ $teams[$action->teamID]['color'] ?? '#aaa' }};"></i> Shot
                    @elseif($action->typeID == 3)
                        <i class="fa-solid fa-hand" style="color:{{ $teams[$action->teamID]['color'] ?? '#aaa' }};"></i> Save
                    @elseif($action->typeID == 5)
                        <i class="fa-solid fa-square" style="color:#f0c040;"></i> Yellow card
                    @elseif($action->typeID == 6)
                        <i class="fa-solid fa-square" style="color:#e24b4a;"></i> Red card
                    @elseif($action->typeID == 7)
                        <i class="fa-solid fa-square" style="color:#fff; -webkit-text-stroke:1px #ccc;"></i> White card
                    @endif
                </td>
                <td>{{ $action->scorerFirst }} {{ $action->scorerLast }}</td>
                <td>{{ $action->assisterFirst }} {{ $action->assisterLast }}</td>
                @if($youtubeID && $youtubeStart)
                <td>
                    @php
                        $offset = \Carbon\Carbon::parse($youtubeStart)->diffInSeconds(\Carbon\Carbon::parse($action->actionTime));
                    @endphp
                    <a href="https://www.youtube.com/watch?v={{ $youtubeID }}&t={{ $offset }}s" target="_blank" style="color:#e24b4a; font-size:12px; text-decoration:none;">
                        <i class="fa-brands fa-youtube"></i> Watch
                    </a>
                </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#events-table').DataTable({
            pageLength: 25,
            order: [],
            language: {
                search: 'Search events:',
                lengthMenu: 'Show _MENU_ events',
            }
        });
    });
</script>
@endpush