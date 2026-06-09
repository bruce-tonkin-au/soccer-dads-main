<div>
<style>
    .wc-page { background:#fff; }
    .wc-hero { background:#262c39; padding:3.5rem 2rem 3rem; }
    .wc-hero .container { max-width:1100px; margin:0 auto; padding:0 2rem; }
    .wc-hero h1 { font-family:'GetShow'; font-weight:normal; font-size:60px; line-height:1.05; color:#fff; }

    .wc-wrap { max-width:1100px; margin:0 auto; padding:2.5rem 2rem 4rem; }

    .wc-tabs { display:flex; gap:2px; border-bottom:1px solid #eceef1; margin-bottom:2rem; }
    .wc-tab { padding:12px 22px; font-size:14px; font-weight:700; color:#9aa1ad; cursor:pointer; border:none; background:none; border-bottom:2px solid transparent; letter-spacing:.02em; }
    .wc-tab:hover { color:#262c39; }
    .wc-tab.active { color:#262c39; border-bottom-color:#458bc8; }

    .wc-empty { text-align:center; background:#f6f7f9; border:1px solid #eceef1; border-radius:16px; padding:3.5rem 2rem; }
    .wc-empty .wc-emoji { font-size:44px; }
    .wc-empty h3 { font-size:22px; color:#262c39; margin:.75rem 0 .35rem; }
    .wc-empty p { color:#667085; font-size:15px; }

    .wc-tablewrap { overflow-x:auto; border:1px solid #eceef1; border-radius:16px; }
    table.wc-table { width:100%; border-collapse:collapse; font-size:14px; min-width:820px; }
    .wc-table thead th { text-align:left; font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#9aa1ad; padding:14px 16px; border-bottom:1px solid #eceef1; white-space:nowrap; }
    .wc-table tbody td { padding:16px; border-bottom:1px solid #f1f2f4; vertical-align:top; }
    .wc-table tbody tr:last-child td { border-bottom:none; }
    .wc-table tbody tr.wc-r1 { background:rgba(245,193,66,.10); }
    .wc-table tbody tr.wc-r2 { background:rgba(160,168,180,.12); }
    .wc-table tbody tr.wc-r3 { background:rgba(196,122,72,.10); }

    .wc-pos { font-size:22px; font-weight:800; color:#262c39; text-align:center; width:54px; }
    .wc-entry { font-weight:700; color:#262c39; font-size:15px; }
    .wc-line { display:flex; align-items:center; gap:8px; padding:2px 0; white-space:nowrap; }
    .wc-line .flag { font-size:18px; }
    .wc-line .grp { font-size:11px; font-weight:700; color:#9aa1ad; }
    .wc-muted { color:#667085; }
    .wc-badge { display:inline-flex; align-items:center; gap:3px; background:#e24b4a; color:#fff; font-size:11px; font-weight:700; padding:1px 7px; border-radius:9px; }

    .wc-pts { white-space:nowrap; }
    .wc-pts .wc-total { font-size:20px; font-weight:800; color:#262c39; }

    .wc-cards { display:grid; gap:12px; }
    .wc-card { border:1px solid #eceef1; border-radius:14px; padding:16px 18px; }
    .wc-row { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
    .wc-teams { display:flex; align-items:center; gap:14px; font-size:15px; font-weight:600; color:#262c39; }
    .wc-teams .score { font-weight:800; color:#262c39; background:#f4f4f4; border-radius:8px; padding:2px 10px; }
    .wc-teams .vs { color:#c2c6cd; font-weight:700; font-size:12px; }
    .wc-meta { text-align:right; }
    .wc-grp-pill { display:inline-block; font-size:11px; font-weight:700; color:#262c39; background:#eef0f3; border-radius:7px; padding:1px 8px; margin-bottom:3px; }
    .wc-when { font-size:13px; color:#667085; }
    .wc-scorers { margin-top:8px; font-size:13px; color:#667085; }

    .wc-block { margin-top:12px; border-top:1px dashed #eceef1; padding-top:10px; }
    .wc-block .lbl { font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#9aa1ad; margin-bottom:5px; }
    .wc-award { font-size:13.5px; color:#475467; padding:1px 0; }
    .wc-award strong { color:#262c39; }
    .wc-award .pts { color:#2e7d32; font-weight:700; }
    .wc-watch { font-size:13.5px; color:#475467; line-height:1.6; }
    .wc-watch strong { color:#262c39; }

    .wc-key { text-align:center; color:#667085; font-size:14px; border-top:1px solid #eceef1; padding-top:1.75rem; margin-top:1rem; }
    .wc-key strong { color:#262c39; }

    @media (max-width:600px) {
        .wc-hero h1 { font-size:42px; }
        .wc-tab { padding:12px 14px; }
    }
</style>

<div class="wc-page">
    {{-- Header --}}
    <div class="wc-hero" style="background:linear-gradient(to right, #458bc8, #7bba56, #e68a46);">
        <div class="container">
            <h1 style="color:#fff;">World Cup 2026</h1>
        </div>
    </div>

    <div class="wc-wrap">

        {{-- Tabs --}}
        <div class="wc-tabs">
            <button type="button" class="wc-tab {{ $activeTab === 'ladder' ? 'active' : '' }}" wire:click="$set('activeTab', 'ladder')">Ladder</button>
            <button type="button" class="wc-tab {{ $activeTab === 'results' ? 'active' : '' }}" wire:click="$set('activeTab', 'results')">Results</button>
            <button type="button" class="wc-tab {{ $activeTab === 'upcoming' ? 'active' : '' }}" wire:click="$set('activeTab', 'upcoming')">Upcoming</button>
        </div>

        {{-- TAB 1: LADDER --}}
        @if ($activeTab === 'ladder')
            @if ($ladder->isEmpty())
                <div class="wc-empty">
                    <div class="wc-emoji">📋</div>
                    <h3>No entries yet</h3>
                    <p>Entries appear here as people join the sweepstake.</p>
                </div>
            @else
                <div class="wc-tablewrap">
                    <table class="wc-table">
                        <thead>
                            <tr>
                                <th style="text-align:center;">#</th>
                                <th>Entry</th>
                                <th>Top 24</th>
                                <th>Bottom 24</th>
                                <th>Players</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ladder as $row)
                                <tr class="@if($row['position'] && $row['position'] <= 3) wc-r{{ $row['position'] }} @endif">
                                    <td class="wc-pos">
                                        @if (! $row['position'])
                                            <span class="wc-muted">—</span>
                                        @else
                                            @switch($row['position'])
                                                @case(1) 🥇 @break
                                                @case(2) 🥈 @break
                                                @case(3) 🥉 @break
                                                @default {{ $row['position'] }}
                                            @endswitch
                                        @endif
                                    </td>
                                    <td><span class="wc-entry">{{ $row['member_name'] }}</span></td>
                                    @foreach (['top_team', 'bottom_team'] as $key)
                                        <td>
                                            @php $team = $row[$key]; @endphp
                                            @if ($team)
                                                <div class="wc-line">
                                                    <span class="flag">{{ $team['flag'] }}</span>
                                                    <span>{{ $team['name'] }}</span>
                                                    @if ($team['group_letter'])<span class="grp">{{ $team['group_letter'] }}</span>@endif
                                                </div>
                                            @else
                                                <span class="wc-muted">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td>
                                        @forelse ($row['players'] as $player)
                                            <div class="wc-line">
                                                <span class="flag">{{ $player['flag'] }}</span>
                                                <span>{{ $player['name'] }}</span>
                                                @if ($player['goal_count'] > 0)<span class="wc-badge">⚽ {{ $player['goal_count'] }}</span>@endif
                                            </div>
                                        @empty
                                            <span class="wc-muted">—</span>
                                        @endforelse
                                    </td>
                                    <td class="wc-pts"><span class="wc-total">{{ $row['total_points'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif

        {{-- TAB 2: RESULTS --}}
        @if ($activeTab === 'results')
            @if ($results->isEmpty())
                <div class="wc-empty">
                    <div class="wc-emoji">⚽</div>
                    <h3>No results yet</h3>
                    <p>No results yet — first match kicks off Fri 12 Jun, 4:30am ACST</p>
                </div>
            @else
                <div class="wc-cards">
                    @foreach ($results as $result)
                        <div class="wc-card">
                            <div class="wc-row">
                                <div class="wc-teams">
                                    <span>{{ $result['home_flag'] }} {{ $result['home_name'] }}</span>
                                    <span class="score">{{ $result['home_score'] }} : {{ $result['away_score'] }}</span>
                                    <span>{{ $result['away_flag'] }} {{ $result['away_name'] }}</span>
                                </div>
                                <div class="wc-meta">
                                    @if ($result['group_letter'])<div class="wc-grp-pill">Group {{ $result['group_letter'] }}</div>@endif
                                    <div class="wc-when">{{ optional($result['date'])->copy()?->timezone('Australia/Adelaide')?->format('D j M') }}</div>
                                </div>
                            </div>

                            @if ($result['scorers'] !== '')
                                <div class="wc-scorers">⚽ {{ $result['scorers'] }}</div>
                            @endif

                            @if (! empty($result['awards']))
                                <div class="wc-block">
                                    <div class="lbl">Points awarded</div>
                                    @foreach ($result['awards'] as $award)
                                        <div class="wc-award">
                                            <strong>{{ $award['entry_name'] }}</strong>
                                            <span class="pts">+{{ $award['points'] }}pts</span>
                                            <span class="wc-muted">({{ $award['reason'] }})</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        {{-- TAB 3: UPCOMING --}}
        @if ($activeTab === 'upcoming')
            @if ($upcoming->isEmpty())
                <div class="wc-empty">
                    <div class="wc-emoji">📅</div>
                    <h3>No upcoming fixtures</h3>
                    <p>Every scheduled match has been played.</p>
                </div>
            @else
                <div class="wc-cards">
                    @foreach ($upcoming as $fixture)
                        <div class="wc-card">
                            <div class="wc-row">
                                <div class="wc-teams">
                                    <span>{{ $fixture['home_flag'] }} {{ $fixture['home_name'] }}</span>
                                    <span class="vs">VS</span>
                                    <span>{{ $fixture['away_flag'] }} {{ $fixture['away_name'] }}</span>
                                </div>
                                <div class="wc-meta">
                                    @if ($fixture['group_letter'])<div class="wc-grp-pill">Group {{ $fixture['group_letter'] }}</div>@endif
                                    <div class="wc-when">
                                        {{ optional($fixture['datetime'])->copy()?->timezone('Australia/Adelaide')?->format('D j M, g:ia') }} ACST
                                    </div>
                                </div>
                            </div>

                            @if (! empty($fixture['team_watchers']) || ! empty($fixture['player_watchers']))
                                <div class="wc-block">
                                    @if (! empty($fixture['team_watchers']))
                                        <div class="wc-watch">
                                            🎯
                                            @foreach ($fixture['team_watchers'] as $w)
                                                <strong>{{ $w['name'] }}</strong> ({{ $w['team'] }}){{ ! $loop->last ? ' · ' : '' }}
                                            @endforeach
                                        </div>
                                    @endif
                                    @if (! empty($fixture['player_watchers']))
                                        <div class="wc-watch" style="margin-top:6px;">
                                            ⚽
                                            @foreach ($fixture['player_watchers'] as $w)
                                                <strong>{{ $w['name'] }}</strong> ({{ $w['player'] }}){{ ! $loop->last ? ' · ' : '' }}
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        {{-- Points key — always visible --}}
        <div class="wc-key">
            Win = <strong>{{ $pointsKey['win'] }}pts</strong>
            · Draw = <strong>{{ $pointsKey['draw'] }}pt{{ $pointsKey['draw'] == 1 ? '' : 's' }}</strong>
            · Goal = <strong>{{ $pointsKey['goal'] }}pts</strong>
        </div>

    </div>
</div>
</div>
