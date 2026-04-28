@extends('layouts.app')

@section('title', 'Round ' . $game->gameRound . ' — ' . $season->seasonName . ' — Soccer Dads')

@section('content')

<div style="padding:4rem 2rem;">
    <div class="container">

        <a href="/seasons/{{ $season->seasonLink }}" style="font-size:13px; color:#888; text-decoration:none; display:inline-flex; align-items:center; gap:6px; margin-bottom:1.5rem;">
            <i class="fa-solid fa-chevron-left"></i> {{ $season->seasonName }}
        </a>

        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:64px; color:#262c39; margin-bottom:0.25rem;">
            Round {{ $game->gameRound }}
        </h1>
        <p style="font-size:15px; color:#888; margin-bottom:2rem;">
            {{ \Carbon\Carbon::parse($game->gameDate)->format('l j F Y') }}
        </p>

        @if($results->count() > 0 && $results->first() && isset($results->first()->homeTeam))
{{-- Team cards --}}
@php
    // Calculate team totals for the night
    $teamNightGoals = [];
    $teamNightPoints = [];
    foreach($results as $r) {
        $homeID = $r->homeTeam['id'];
        $awayID = $r->awayTeam['id'];
        $teamNightGoals[$homeID] = ($teamNightGoals[$homeID] ?? 0) + $r->homeGoals;
        $teamNightGoals[$awayID] = ($teamNightGoals[$awayID] ?? 0) + $r->awayGoals;
        if ($r->homeGoals > $r->awayGoals) {
            $teamNightPoints[$homeID] = ($teamNightPoints[$homeID] ?? 0) + 2;
        } elseif ($r->awayGoals > $r->homeGoals) {
            $teamNightPoints[$awayID] = ($teamNightPoints[$awayID] ?? 0) + 2;
        } else {
            $teamNightPoints[$homeID] = ($teamNightPoints[$homeID] ?? 0) + 1;
            $teamNightPoints[$awayID] = ($teamNightPoints[$awayID] ?? 0) + 1;
        }
    }
    $teamIDs = [1, 2, 3];
    usort($teamIDs, function($a, $b) use ($teamNightPoints, $teamNightGoals) {
        $pointsDiff = ($teamNightPoints[$b] ?? 0) - ($teamNightPoints[$a] ?? 0);
        if ($pointsDiff !== 0) return $pointsDiff;
        return ($teamNightGoals[$b] ?? 0) - ($teamNightGoals[$a] ?? 0);
    });
    $positions = ['1st', '2nd', '3rd'];
@endphp

        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:2rem; align-items:stretch;">
            @foreach($teamIDs as $i => $teamID)
            @php
                $team = $teams[$teamID];
                $teamGoalScorers = $actions->where('actionGoal', 1)->where('teamID', $teamID)->whereNotNull('memberID');
                $scorerCounts = $teamGoalScorers->groupBy('memberID')->map(fn($g) => [
                    'name' => $g->first()->scorerFirst . ' ' . $g->first()->scorerLast,
                    'goals' => $g->count()
                ])->sortByDesc('goals')->keyBy('name');
                $players = $teamPlayers[$teamID] ?? collect();
            @endphp
            <div style="background:{{ $team['color'] }}; border-radius:16px; padding:1.5rem; color:#fff; display:flex; flex-direction:column;">
                <div style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.12em; color:rgba(255,255,255,0.7); margin-bottom:4px;">{{ $positions[$i] }}</div>
                <div style="font-size:48px; font-weight:700; line-height:1; margin-bottom:4px;">{{ $teamNightPoints[$teamID] ?? 0 }}</div>
                <div style="font-size:18px; font-weight:600; margin-bottom:4px;">{{ $team['name'] }}</div>
                <div style="font-size:12px; color:rgba(255,255,255,0.7); margin-bottom:1rem;">{{ $teamNightGoals[$teamID] ?? 0 }} goals</div>
                <div style="border-top:1px solid rgba(255,255,255,0.2); padding-top:1rem; flex:1;">
                    @forelse($players as $player)
                    @php $fullName = $player->memberNameFirst . ' ' . $player->memberNameLast; @endphp
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:13px; margin-bottom:6px;">
                        <span style="color:rgba(255,255,255,0.9);">{{ $fullName }}</span>
                        @if(isset($scorerCounts[$fullName]))
                        <span style="background:rgba(255,255,255,0.2); padding:2px 8px; border-radius:20px; font-size:12px; font-weight:600;">{{ $scorerCounts[$fullName]['goals'] }}</span>
                        @endif
                    </div>
                    @empty
                    <div style="font-size:13px; color:rgba(255,255,255,0.5);">No players</div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
@endif

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

        {{-- Results --}}
        <h2 style="font-size:18px; font-weight:600; color:#262c39; margin-bottom:1rem;">Results</h2>
        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-bottom:2rem;">
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <thead>
                    <tr style="background:#f8f8f8; border-bottom:1px solid #e8e8e8;">
                        <th style="padding:10px 16px; text-align:left; font-weight:600; color:#262c39;">Round</th>
                        <th style="padding:10px 16px; text-align:center; font-weight:600; color:#262c39;">Game 1</th>
                        <th style="padding:10px 16px; text-align:center; font-weight:600; color:#262c39;">Game 2</th>
                        <th style="padding:10px 16px; text-align:center; font-weight:600; color:#262c39;">Game 3</th>
                        <th style="padding:10px 16px; text-align:center; font-weight:600; color:#262c39;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results->groupBy('scoringRound') as $round => $games)
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:10px 16px; font-weight:500; color:#262c39;">{{ $round }}</td>
                        @for($g = 1; $g <= 3; $g++)
                        @php $game = $games->firstWhere('scoringGame', $g); @endphp
                        <td style="padding:10px 16px; text-align:center; white-space:nowrap;">
                            @if($game)
                            <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:{{ $game->homeTeam['color'] }}; margin-right:4px;"></span>{{ $game->homeTeam['name'] }} <strong>{{ $game->homeGoals }}</strong> – <strong>{{ $game->awayGoals }}</strong> {{ $game->awayTeam['name'] }}<span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:{{ $game->awayTeam['color'] }}; margin-left:4px;"></span>
                            @endif
                        </td>
                        @endfor
                        @php
                            $teamPoints = [1 => 0, 2 => 0, 3 => 0];
                            foreach($games as $g) {
                                $homeID = $g->homeTeam['id'];
                                $awayID = $g->awayTeam['id'];
                                if ($g->homeGoals > $g->awayGoals) {
                                    $teamPoints[$homeID] = ($teamPoints[$homeID] ?? 0) + 2;
                                } elseif ($g->awayGoals > $g->homeGoals) {
                                    $teamPoints[$awayID] = ($teamPoints[$awayID] ?? 0) + 2;
                                } else {
                                    $teamPoints[$homeID] = ($teamPoints[$homeID] ?? 0) + 1;
                                    $teamPoints[$awayID] = ($teamPoints[$awayID] ?? 0) + 1;
                                }
                            }
                        @endphp
                        <td style="padding:10px 16px; text-align:center;">
                            <span style="background:#458bc8; color:#fff; padding:3px 10px; border-radius:20px; font-size:13px; font-weight:600; margin:2px;">{{ $teamPoints[3] ?? 0 }}</span>
                            <span style="background:#7bba56; color:#fff; padding:3px 10px; border-radius:20px; font-size:13px; font-weight:600; margin:2px;">{{ $teamPoints[2] ?? 0 }}</span>
                            <span style="background:#e68a46; color:#fff; padding:3px 10px; border-radius:20px; font-size:13px; font-weight:600; margin:2px;">{{ $teamPoints[1] ?? 0 }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Points timeline chart --}}
        <h2 style="font-size:18px; font-weight:600; color:#262c39; margin-bottom:1rem;">Points Timeline</h2>
        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; padding:1.5rem; margin-bottom:2rem;">
            <canvas id="timelineChart"></canvas>
        </div>

        {{-- Goals and assists --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:2rem; margin-bottom:2rem;">

            {{-- Goals --}}
            <div>
                <h2 style="font-size:18px; font-weight:600; color:#262c39; margin-bottom:1rem;">Goals</h2>
                <div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden;">
                    <table style="width:100%; border-collapse:collapse; font-size:14px;">
                        <thead>
                            <tr style="background:#f8f8f8; border-bottom:1px solid #e8e8e8;">
                                <th style="padding:10px 16px; text-align:left; font-weight:600; color:#262c39;">Player</th>
                                <th style="padding:10px 16px; text-align:center; font-weight:600; color:#262c39;">Today</th>
                                <th style="padding:10px 16px; text-align:center; font-weight:600; color:#262c39;">STD</th>
                                <th style="padding:10px 16px; text-align:center; font-weight:600; color:#262c39;">YTD</th>
                                <th style="padding:10px 16px; text-align:center; font-weight:600; color:#262c39;">AT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $goalActions = $actions->where('actionGoal', 1)
                                ->whereNotNull('memberID')
                                ->groupBy('memberID')
                                ->map(fn($g) => $g->first())
                                ->sortByDesc(fn($a) => $nightGoals[$a->memberID] ?? 0)
                                ->take(10);
                            @endphp
                            @forelse($goalActions as $memberID => $action)
                            <tr style="border-bottom:1px solid #f0f0f0;">
                                <td style="padding:10px 16px; color:#262c39;">
                                    @if($action->scorerSlug)
                                    <a href="/players/{{ $action->scorerSlug }}" style="color:#262c39; text-decoration:none; font-weight:500;">{{ $action->scorerFirst }} {{ $action->scorerLast }}</a>
                                    @else
                                    {{ $action->scorerFirst }} {{ $action->scorerLast }}
                                    @endif
                                </td>
                                <td style="padding:10px 16px; text-align:center; font-weight:600; color:#262c39;">{{ $nightGoals[$memberID] ?? 0 }}</td>
                                <td style="padding:10px 16px; text-align:center; color:#888;">{{ $seasonGoals[$memberID] ?? 0 }}</td>
                                <td style="padding:10px 16px; text-align:center; color:#888;">{{ $ytdGoals[$memberID] ?? 0 }}</td>
                                <td style="padding:10px 16px; text-align:center; color:#888;">{{ $allTimeGoals[$memberID] ?? 0 }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" style="padding:16px; text-align:center; color:#aaa;">No goals recorded</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Assists --}}
            <div>
                <h2 style="font-size:18px; font-weight:600; color:#262c39; margin-bottom:1rem;">Assists</h2>
                <div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden;">
                    <table style="width:100%; border-collapse:collapse; font-size:14px;">
                        <thead>
                            <tr style="background:#f8f8f8; border-bottom:1px solid #e8e8e8;">
                                <th style="padding:10px 16px; text-align:left; font-weight:600; color:#262c39;">Player</th>
                                <th style="padding:10px 16px; text-align:center; font-weight:600; color:#262c39;">Today</th>
                                <th style="padding:10px 16px; text-align:center; font-weight:600; color:#262c39;">STD</th>
                                <th style="padding:10px 16px; text-align:center; font-weight:600; color:#262c39;">YTD</th>
                                <th style="padding:10px 16px; text-align:center; font-weight:600; color:#262c39;">AT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $assistActions = $actions->where('actionGoal', 1)
                                ->whereNotNull('secondID')
                                ->groupBy('secondID')
                                ->map(fn($g) => $g->first())
                                ->sortByDesc(fn($a) => $seasonAssists[$a->secondID] ?? 0)
                                ->take(10);
                            @endphp
                            @forelse($assistActions as $secondID => $action)
                            <tr style="border-bottom:1px solid #f0f0f0;">
                                <td style="padding:10px 16px; color:#262c39;">
                                    @if($action->assisterSlug)
                                    <a href="/players/{{ $action->assisterSlug }}" style="color:#262c39; text-decoration:none; font-weight:500;">{{ $action->assisterFirst }} {{ $action->assisterLast }}</a>
                                    @else
                                    {{ $action->assisterFirst }} {{ $action->assisterLast }}
                                    @endif
                                </td>
                                <td style="padding:10px 16px; text-align:center; font-weight:600; color:#262c39;">{{ $actions->where('actionGoal', 1)->where('secondID', $secondID)->count() }}</td>
                                <td style="padding:10px 16px; text-align:center; color:#888;">{{ $seasonAssists[$secondID] ?? 0 }}</td>
                                <td style="padding:10px 16px; text-align:center; color:#888;">{{ $ytdAssists[$secondID] ?? 0 }}</td>
                                <td style="padding:10px 16px; text-align:center; color:#888;">{{ $allTimeAssists[$secondID] ?? 0 }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" style="padding:16px; text-align:center; color:#aaa;">No assists recorded</td></tr>
                            @endforelse
                        </tbody>
                    </table>
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
                        <td>
                            @if($action->scorerSlug)
                            <a href="/players/{{ $action->scorerSlug }}" style="color:#262c39; text-decoration:none; font-weight:500;">{{ $action->scorerFirst }} {{ $action->scorerLast }}</a>
                            @else
                            {{ $action->scorerFirst }} {{ $action->scorerLast }}
                            @endif
                        </td>
                        <td>
                            @if($action->assisterSlug)
                            <a href="/players/{{ $action->assisterSlug }}" style="color:#262c39; text-decoration:none; font-weight:500;">{{ $action->assisterFirst }} {{ $action->assisterLast }}</a>
                            @else
                            {{ $action->assisterFirst }} {{ $action->assisterLast }}
                            @endif
                        </td>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    // Build timeline data
    const results = @json($results->values());

    const labels = [];
    const bluePoints = [0];
    const greenPoints = [0];
    const orangePoints = [0];

    let blue = 0, green = 0, orange = 0;

    results.forEach(game => {
        labels.push('R' + game.scoringRound + ' G' + game.scoringGame);

        const homeID = game.homeTeam.id;
        const awayID = game.awayTeam.id;
        const homeGoals = game.homeGoals;
        const awayGoals = game.awayGoals;

        let homePoints = 0, awayPoints = 0;
        if (homeGoals > awayGoals) {
            homePoints = 2;
        } else if (awayGoals > homeGoals) {
            awayPoints = 2;
        } else {
            homePoints = 1;
            awayPoints = 1;
        }

        if (homeID == 3) blue += homePoints;
        if (homeID == 2) green += homePoints;
        if (homeID == 1) orange += homePoints;
        if (awayID == 3) blue += awayPoints;
        if (awayID == 2) green += awayPoints;
        if (awayID == 1) orange += awayPoints;

        bluePoints.push(blue);
        greenPoints.push(green);
        orangePoints.push(orange);
    });

    labels.unshift('Start');

    const ctx = document.getElementById('timelineChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Blue',
                    data: bluePoints,
                    borderColor: '#458bc8',
                    backgroundColor: 'rgba(69,139,200,0.1)',
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#458bc8',
                    tension: 0,
                    fill: false,
                },
                {
                    label: 'Green',
                    data: greenPoints,
                    borderColor: '#7bba56',
                    backgroundColor: 'rgba(123,186,86,0.1)',
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#7bba56',
                    tension: 0,
                    fill: false,
                },
                {
                    label: 'Orange',
                    data: orangePoints,
                    borderColor: '#e68a46',
                    backgroundColor: 'rgba(230,138,70,0.1)',
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#e68a46',
                    tension: 0,
                    fill: false,
                },
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    },
                    title: {
                        display: true,
                        text: 'Points'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Game'
                    }
                }
            }
        }
    });
</script>
@endpush