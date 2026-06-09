<div>
<style>
    .wc-page { background:#fff; }
    .wc-hero { background:#262c39; padding:3.5rem 2rem 3rem; }
    .wc-hero .container { max-width:1100px; margin:0 auto; padding:0 2rem; }
    .wc-hero h1 { font-family:'GetShow'; font-weight:normal; font-size:60px; line-height:1.05; color:#fff; }
    .wc-hero .wc-sub { margin-top:.5rem; font-size:14px; font-weight:600; letter-spacing:.14em; text-transform:uppercase; color:rgba(255,255,255,.45); }

    .wc-wrap { max-width:1100px; margin:0 auto; padding:3rem 2rem 4rem; }
    .wc-section { margin-bottom:3rem; }
    .wc-section h2 { font-size:13px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:#9aa1ad; margin-bottom:1rem; }

    .wc-empty { text-align:center; background:#f6f7f9; border:1px solid #eceef1; border-radius:16px; padding:3.5rem 2rem; }
    .wc-empty .wc-emoji { font-size:44px; }
    .wc-empty h3 { font-size:22px; color:#262c39; margin:.75rem 0 .35rem; }
    .wc-empty p { color:#667085; font-size:15px; }

    .wc-tablewrap { overflow-x:auto; border:1px solid #eceef1; border-radius:16px; }
    table.wc-table { width:100%; border-collapse:collapse; font-size:14px; min-width:720px; }
    .wc-table thead th { text-align:left; font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#9aa1ad; padding:14px 16px; border-bottom:1px solid #eceef1; white-space:nowrap; }
    .wc-table tbody td { padding:16px; border-bottom:1px solid #f1f2f4; vertical-align:middle; }
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
    .wc-pts .wc-sub2 { font-size:12px; color:#9aa1ad; }
    .wc-pts .wc-total { font-size:20px; font-weight:800; color:#262c39; }

    .wc-cards { display:grid; gap:12px; }
    .wc-card { border:1px solid #eceef1; border-radius:14px; padding:14px 18px; }
    .wc-result { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
    .wc-result .teams { display:flex; align-items:center; gap:14px; font-size:15px; font-weight:600; color:#262c39; }
    .wc-result .score { font-weight:800; color:#262c39; background:#f4f4f4; border-radius:8px; padding:2px 10px; }
    .wc-result .date { font-size:12px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:#9aa1ad; }
    .wc-scorers { margin-top:8px; font-size:13px; color:#667085; }

    .wc-fixture { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
    .wc-fixture .teams { display:flex; align-items:center; gap:12px; font-size:15px; font-weight:600; color:#262c39; }
    .wc-fixture .vs { color:#c2c6cd; font-weight:700; font-size:12px; }
    .wc-fixture .meta { text-align:right; }
    .wc-fixture .grp-pill { display:inline-block; font-size:11px; font-weight:700; color:#262c39; background:#eef0f3; border-radius:7px; padding:1px 8px; margin-bottom:3px; }
    .wc-fixture .when { font-size:13px; color:#667085; }

    .wc-key { text-align:center; color:#667085; font-size:14px; border-top:1px solid #eceef1; padding-top:1.75rem; }
    .wc-key strong { color:#262c39; }

    @media (max-width:600px) {
        .wc-hero h1 { font-size:42px; }
    }
</style>

<div class="wc-page">
    {{-- 1. Header --}}
    <div class="wc-hero" style="background:linear-gradient(to right, #458bc8, #7bba56, #e68a46);">
        <div class="container">
            <h1 style="color:#fff;">World Cup 2026</h1>
        </div>
    </div>

    <div class="wc-wrap">

        {{-- 2 & 3. Ladder (or pre-draw message) --}}
        <div class="wc-section">
            <h2>The Ladder</h2>

            @if (! $drawRun)
                <div class="wc-empty">
                    <div class="wc-emoji">🎩</div>
                    <h3>Draw happens Friday night</h3>
                    <p>Check back soon — once the teams and players are pulled from the hat, the ladder appears here.</p>
                </div>
            @elseif ($ladder->isEmpty())
                <div class="wc-empty">
                    <div class="wc-emoji">📋</div>
                    <h3>No entries yet</h3>
                    <p>The draw is underway — entries will show here as they’re completed.</p>
                </div>
            @else
                <div class="wc-tablewrap">
                    <table class="wc-table">
                        <thead>
                            <tr>
                                <th style="text-align:center;">#</th>
                                <th>Entry</th>
                                <th>Teams</th>
                                <th>Players</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ladder as $row)
                                <tr class="@if($row['position']<=3) wc-r{{ $row['position'] }} @endif">
                                    <td class="wc-pos">
                                        @switch($row['position'])
                                            @case(1) 🥇 @break
                                            @case(2) 🥈 @break
                                            @case(3) 🥉 @break
                                            @default {{ $row['position'] }}
                                        @endswitch
                                    </td>
                                    <td><span class="wc-entry">{{ $row['entry_name'] }}</span></td>
                                    <td>
                                        @foreach (['top_team' => 'Top 24', 'bottom_team' => 'Bottom 24'] as $key => $label)
                                            @php $team = $row[$key]; @endphp
                                            <div class="wc-line">
                                                @if ($team)
                                                    <span class="flag">{{ $team['flag'] }}</span>
                                                    <span>{{ $team['name'] }}</span>
                                                    @if ($team['group_letter'])
                                                        <span class="grp">{{ $team['group_letter'] }}</span>
                                                    @endif
                                                @else
                                                    <span class="wc-muted">—</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>
                                        @forelse ($row['players'] as $player)
                                            <div class="wc-line">
                                                <span class="flag">{{ $player['flag'] }}</span>
                                                <span>{{ $player['name'] }}</span>
                                                @if ($player['goal_count'] > 0)
                                                    <span class="wc-badge">⚽ {{ $player['goal_count'] }}</span>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="wc-line wc-muted">—</div>
                                        @endforelse
                                    </td>
                                    <td class="wc-pts">
                                        <div class="wc-sub2">{{ $row['team_points'] }} team + {{ $row['player_points'] }} player</div>
                                        <div class="wc-total">{{ $row['total_points'] }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- 4. Recent results --}}
        @if ($recentResults->isNotEmpty())
            <div class="wc-section">
                <h2>Recent Results</h2>
                <div class="wc-cards">
                    @foreach ($recentResults as $result)
                        <div class="wc-card">
                            <div class="wc-result">
                                <div class="teams">
                                    <span>{{ $result['home_flag'] }} {{ $result['home_name'] }}</span>
                                    <span class="score">{{ $result['home_score'] }} : {{ $result['away_score'] }}</span>
                                    <span>{{ $result['away_flag'] }} {{ $result['away_name'] }}</span>
                                </div>
                                <div class="date">
                                    {{ optional($result['date'])->copy()?->timezone('Australia/Adelaide')?->format('D j M') }}
                                </div>
                            </div>
                            @if ($result['scorers'] !== '')
                                <div class="wc-scorers">⚽ {{ $result['scorers'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- 5. Upcoming fixtures --}}
        @if ($upcomingFixtures->isNotEmpty())
            <div class="wc-section">
                <h2>Upcoming Fixtures</h2>
                <div class="wc-cards">
                    @foreach ($upcomingFixtures as $fixture)
                        <div class="wc-card">
                            <div class="wc-fixture">
                                <div class="teams">
                                    <span>{{ $fixture['home_flag'] }} {{ $fixture['home_name'] }}</span>
                                    <span class="vs">VS</span>
                                    <span>{{ $fixture['away_flag'] }} {{ $fixture['away_name'] }}</span>
                                </div>
                                <div class="meta">
                                    @if ($fixture['group_letter'])
                                        <div class="grp-pill">Group {{ $fixture['group_letter'] }}</div>
                                    @endif
                                    <div class="when">
                                        {{ optional($fixture['datetime'])->copy()?->timezone('Australia/Adelaide')?->format('D j M, g:ia') }}
                                        <span class="wc-muted">ACST</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- 6. Points key --}}
        <div class="wc-key">
            Win = <strong>{{ $pointsKey['win'] }}pts</strong>
            · Draw = <strong>{{ $pointsKey['draw'] }}pt{{ $pointsKey['draw'] == 1 ? '' : 's' }}</strong>
            · Goal = <strong>{{ $pointsKey['goal'] }}pts</strong>
        </div>

    </div>
</div>
</div>
