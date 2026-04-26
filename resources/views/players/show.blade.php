@extends('layouts.app')

@section('title', $member->memberNameFirst . ' ' . $member->memberNameLast . ' — Soccer Dads')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    /* Hero */
    .player-show-hero {
        background: #262c39;
        padding: 4rem 2rem 3rem;
    }
    .player-show-hero h1 {
        font-family: 'GetShow';
        font-weight: normal;
        font-size: 72px;
        color: #fff;
        line-height: 1;
        margin: 0.75rem 0 0;
    }
    .player-show-body {
        padding: 3rem 2rem 4rem;
    }

    /* Stat cards */
    .stat-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 1.25rem;
    }
    .stat-card {
        border-radius: 12px;
        padding: 1.5rem 1.25rem;
        text-align: center;
        color: #fff;
    }
    .stat-card-value {
        font-size: 48px;
        font-weight: 700;
        line-height: 1;
    }
    .stat-card-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        opacity: 0.8;
        margin-top: 10px;
    }

    /* Section headings */
    .section-heading {
        font-size: 18px;
        font-weight: 600;
        color: #262c39;
        margin-bottom: 1rem;
    }

    /* DataTables shared overrides for this page */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 14px;
        color: #262c39;
        outline: none;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #458bc8;
    }
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        font-size: 13px;
        color: #888;
        margin-bottom: 1rem;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 6px !important;
        padding: 4px 10px !important;
        font-size: 13px !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #262c39 !important;
        color: #fff !important;
        border-color: #262c39 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f4f4f4 !important;
        color: #262c39 !important;
        border-color: #e8e8e8 !important;
    }
    #season-table thead th,
    #actions-table thead th {
        background: #f8f8f8;
        color: #262c39;
        font-size: 13px;
        font-weight: 600;
        padding: 12px 16px;
        border-bottom: 1px solid #e8e8e8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    #season-table tbody td,
    #actions-table tbody td {
        padding: 12px 16px;
        font-size: 14px;
        color: #262c39;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    #season-table tbody tr:hover td,
    #actions-table tbody tr:hover td {
        background: #f8f8f8;
    }
    #season-table tbody tr:last-child td,
    #actions-table tbody tr:last-child td {
        border-bottom: none;
    }

    .table-card {
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 16px;
        overflow: hidden;
        padding: 1.5rem;
        margin-bottom: 2.5rem;
    }
    .team-dot {
        display: inline-block;
        width: 9px;
        height: 9px;
        border-radius: 50%;
        margin-right: 5px;
        vertical-align: middle;
    }

    @media (max-width: 900px) {
        .player-show-hero h1 { font-size: 52px; }
    }
    @media (max-width: 600px) {
        .stat-cards { grid-template-columns: 1fr; }
        .player-show-hero h1 { font-size: 40px; }
        .stat-card-value { font-size: 36px; }
        .dates-grid { grid-template-columns: 1fr !important; }
    }
</style>
@endpush

@section('content')

<div class="player-show-hero">
    <div class="container">
        <a href="/players" style="font-size:13px; color:rgba(255,255,255,0.5); text-decoration:none; display:inline-flex; align-items:center; gap:6px; margin-bottom:1.5rem;">
            <i class="fa-solid fa-chevron-left"></i> All players
        </a>
        <div style="display:flex; align-items:center; justify-content:space-between; gap:1.5rem;">
            <h1>{{ $member->memberNameFirst }} {{ $member->memberNameLast }}</h1>
            @if($member->memberPhoto)
            <img src="{{ Storage::url($member->memberPhoto) }}"
                 style="width:80px; height:80px; border-radius:50%; object-fit:cover; border:3px solid rgba(255,255,255,0.2); flex-shrink:0;">
            @endif
        </div>
    </div>
</div>

<div class="player-show-body">
    <div class="container">

        {{-- Stat cards --}}
        <div class="stat-cards">
            <div class="stat-card" style="background:#458bc8;">
                <div class="stat-card-value">{{ $gamesPlayed }}</div>
                <div class="stat-card-label">Games Played</div>
            </div>
            <div class="stat-card" style="background:#7bba56;">
                <div class="stat-card-value">{{ $goals }}</div>
                <div class="stat-card-label">Goals</div>
            </div>
            <div class="stat-card" style="background:#e68a46;">
                <div class="stat-card-value">{{ $assists }}</div>
                <div class="stat-card-label">Assists</div>
            </div>
        </div>

        {{-- First / Last played --}}
        <div class="dates-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:1.5rem;">
            <div style="background:#fff; border:1px solid #e0e0e0; border-radius:12px; padding:1.25rem 1.5rem;">
                <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:4px;">First played</div>
                <div style="font-size:22px; font-weight:700; color:#262c39;">{{ $firstPlayed ?? '—' }}</div>
            </div>
            <div style="background:#fff; border:1px solid #e0e0e0; border-radius:12px; padding:1.25rem 1.5rem;">
                <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:4px;">Last played</div>
                <div style="font-size:22px; font-weight:700; color:#262c39;">{{ $lastPlayed ?? '—' }}</div>
            </div>
        </div>


        {{-- Awards --}}
        @if($awardHistory->isNotEmpty())
        @php
        $awardStyles = [
            1 => ['color' => '#f0c040', 'icon' => 'fa-trophy', 'label' => '1st'],
            2 => ['color' => '#aaa',    'icon' => 'fa-medal',  'label' => '2nd'],
            3 => ['color' => '#cd7f32', 'icon' => 'fa-medal',  'label' => '3rd'],
        ];
        @endphp
        <h2 class="section-heading">Awards</h2>
        <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:2.5rem;">
            @foreach($awardHistory as $award)
            <div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; padding:1rem; text-align:center; width:110px; flex-shrink:0;">
                <i class="fa-solid {{ $awardStyles[$award->position]['icon'] }}" style="color:{{ $awardStyles[$award->position]['color'] }}; font-size:28px; display:block; margin-bottom:6px;"></i>
                <div style="font-size:13px; font-weight:700; color:#262c39;">{{ $awardStyles[$award->position]['label'] }}</div>
                <div style="font-size:11px; color:#888; margin-top:2px;">
                    @if($award->seasonLink)
                    <a href="/seasons/{{ $award->seasonLink }}" style="color:#888; text-decoration:none;">{{ str_replace(',', '', $award->seasonName) }}</a>
                    @else
                    {{ str_replace(',', '', $award->seasonName) }}
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Season by season (DataTables) --}}
        @if($seasonBreakdown->isNotEmpty())
        <h2 class="section-heading">Season by season</h2>
        <div class="table-card">
            <table id="season-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Season</th>
                        <th>Goals</th>
                        <th>Assists</th>
                        <th>Games</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($seasonBreakdown as $row)
                    <tr>
                        <td data-order="{{ $row->seasonID }}">
                            @if($row->seasonLink)
                            <a href="/seasons/{{ $row->seasonLink }}" style="color:#262c39; text-decoration:none; font-weight:600;">{{ $row->seasonName }}</a>
                            @else
                            <span style="font-weight:600;">{{ $row->seasonName }}</span>
                            @endif
                        </td>
                        <td data-order="{{ $row->goals }}">
                            @if($row->goals > 0)
                            <i class="fa-solid fa-futbol" style="color:#e68a46; margin-right:4px;"></i>{{ $row->goals }}
                            @else
                            <span style="color:#ccc;">—</span>
                            @endif
                        </td>
                        <td data-order="{{ $row->assists }}">
                            @if($row->assists > 0)
                            <i class="fa-solid fa-people-arrows" style="color:#458bc8; margin-right:4px;"></i>{{ $row->assists }}
                            @else
                            <span style="color:#ccc;">—</span>
                            @endif
                        </td>
                        <td data-order="{{ $row->games }}">{{ $row->games > 0 ? $row->games : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Recent Actions (DataTables) --}}
        @if($recentActions->isNotEmpty())
        @php
        $teamColors = [1 => '#e68a46', 2 => '#7bba56', 3 => '#458bc8'];
        $teamNames  = [1 => 'Orange',  2 => 'Green',   3 => 'Blue'];
        $actionDefs = [
            1 => ['icon' => 'fa-solid fa-futbol',   'label' => 'Goal',        'teamColor' => true],
            2 => ['icon' => 'fa-solid fa-bullseye', 'label' => 'Shot',        'teamColor' => true],
            3 => ['icon' => 'fa-solid fa-hand',     'label' => 'Save',        'teamColor' => true],
            5 => ['icon' => 'fa-solid fa-square',   'label' => 'Yellow card', 'teamColor' => false, 'color' => '#f0c040'],
            6 => ['icon' => 'fa-solid fa-square',   'label' => 'Red card',    'teamColor' => false, 'color' => '#e53935'],
            7 => ['icon' => 'fa-regular fa-square', 'label' => 'White card',  'teamColor' => false, 'color' => '#aaa'],
        ];
        @endphp
        <h2 class="section-heading">Recent Actions</h2>
        <div class="table-card">
            <table id="actions-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Season</th>
                        <th>Round</th>
                        <th>Game</th>
                        <th>Team</th>
                        <th>Action</th>
                        <th>Assist by</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentActions as $action)
                    @php
                    $def   = $actionDefs[$action->typeID] ?? ['icon' => 'fa-solid fa-circle', 'label' => 'Action', 'teamColor' => false, 'color' => '#888'];
                    $icolor = ($def['teamColor'] && isset($teamColors[$action->teamID])) ? $teamColors[$action->teamID] : ($def['color'] ?? '#888');
                    @endphp
                    <tr>
                        <td>
                            @if($action->seasonLink)
                            <a href="/seasons/{{ $action->seasonLink }}" style="color:#262c39; text-decoration:none;">{{ $action->seasonName }}</a>
                            @else
                            {{ $action->seasonName }}
                            @endif
                        </td>
                        <td style="color:#888;">{{ $action->gameRound }}</td>
                        <td style="color:#888;">{{ $action->scoringGame }}</td>
                        <td>
                            @if($action->teamID && isset($teamColors[$action->teamID]))
                            <span class="team-dot" style="background:{{ $teamColors[$action->teamID] }};"></span>{{ $teamNames[$action->teamID] }}
                            @else
                            <span style="color:#ccc;">—</span>
                            @endif
                        </td>
                        <td>
                            <i class="{{ $def['icon'] }}" style="color:{{ $icolor }}; margin-right:5px;"></i>{{ $def['label'] }}
                        </td>
                        <td style="color:#888;">
                            @if($action->typeID == 1 && $action->assistFirst)
                            {{ $action->assistFirst }} {{ $action->assistLast }}
                            @else
                            <span style="color:#ccc;">—</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($action->youtubeURL)
                            <a href="{{ $action->youtubeURL }}" target="_blank" rel="noopener" style="color:#ff0000; font-size:18px; text-decoration:none;" title="Watch on YouTube">
                                <i class="fa-brands fa-youtube"></i>
                            </a>
                            @else
                            <span style="color:#e0e0e0;"><i class="fa-brands fa-youtube"></i></span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        @if($seasonBreakdown->isNotEmpty())
        $('#season-table').DataTable({
            pageLength: 10,
            stateSave: true,
            order: [[0, 'desc']],
            columnDefs: [
                { type: 'num', targets: [0, 1, 2, 3] }
            ],
            language: {
                search: 'Search:',
                lengthMenu: 'Show _MENU_ seasons',
                info: 'Showing _START_ to _END_ of _TOTAL_ seasons',
            }
        });
        @endif

        @if($recentActions->isNotEmpty())
        $('#actions-table').DataTable({
            pageLength: 10,
            stateSave: true,
            order: [],
            columnDefs: [
                { orderable: false, targets: [6] }
            ],
            language: {
                search: 'Search:',
                lengthMenu: 'Show _MENU_ actions',
                info: 'Showing _START_ to _END_ of _TOTAL_ actions',
            }
        });
        @endif
    });
</script>
@endpush
