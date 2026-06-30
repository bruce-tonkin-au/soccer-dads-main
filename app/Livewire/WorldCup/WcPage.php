<?php

namespace App\Livewire\WorldCup;

use App\Models\WcEntry;
use App\Models\WcEntryPlayer;
use App\Models\WcEntryTeam;
use App\Models\WcFixture;
use App\Models\WcGoal;
use App\Models\WcPlayer;
use App\Models\WcSetting;
use App\Models\WcTeam;
use App\Support\MemberDirectory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

/**
 * Shared base for the four public World Cup pages (Ladder, Results, Upcoming,
 * Cards). Holds the common header / nav / live-fixture data plus the entry
 * enrichment used to build each page. Each concrete page extends this and
 * implements its own render().
 */
abstract class WcPage extends Component
{
    /**
     * Route name of the page that mounted this component (e.g. 'worldcup.ladder').
     * Captured once at mount so the tab highlight stays correct across Livewire
     * re-renders / polls — request()->routeIs() points at the /livewire/update
     * endpoint during those requests, not the original page route.
     */
    public ?string $activeTab = null;

    /**
     * Memoized set of eliminated teamIDs (teamID => index, for fast has()).
     * Resolved lazily once per request by eliminatedTeamIds().
     */
    protected ?Collection $eliminatedTeamIds = null;

    public function mount(): void
    {
        $this->activeTab = request()->route()?->getName();
    }

    /**
     * Teams that are out of the tournament. A team is eliminated when EITHER
     * an admin has flipped wc_teams.qualified to false (manual override, used
     * before knockout fixtures land), OR the next knockout stage exists and
     * the team has no fixture in it. The union means the manual toggle works
     * straight away and is harmlessly redundant once the draw fills in.
     *
     * Per-request memoised (no persistent cache), so flipping the admin
     * toggle is reflected on the next page load.
     *
     * Returns teamID => index for has().
     */
    protected function eliminatedTeamIds(): Collection
    {
        if ($this->eliminatedTeamIds !== null) {
            return $this->eliminatedTeamIds;
        }

        // Manual eliminations from the admin toggle.
        $manual = WcTeam::query()
            ->where('qualified', false)
            ->pluck('teamID');

        // Fixture-derived eliminations: once round_of_32 is seeded, any team
        // missing from it is out. Before that, this set is empty.
        $r32 = WcFixture::query()
            ->where('stage', 'round_of_32')
            ->get(['home_team_id', 'away_team_id']);

        $fixtureBased = collect();
        if ($r32->isNotEmpty()) {
            $alive = $r32
                ->flatMap(fn (WcFixture $f) => [$f->home_team_id, $f->away_team_id])
                ->filter()
                ->unique();

            $fixtureBased = WcTeam::query()
                ->whereNotIn('teamID', $alive)
                ->pluck('teamID');
        }

        return $this->eliminatedTeamIds = $manual
            ->merge($fixtureBased)
            ->unique()
            ->flip();
    }

    /**
     * Tournament stages, ordered earliest → latest round. Maps the raw
     * wc_fixtures.stage value to its full label (used in the summary line) and
     * a short label (used for the condensed timeline nodes on the progress bar).
     */
    private const STAGES = [
        'group'         => ['label' => 'Group Stage',    'short' => 'Group'],
        'round_of_32'   => ['label' => 'Round of 32',    'short' => 'R32'],
        'round_of_16'   => ['label' => 'Round of 16',    'short' => 'R16'],
        'quarter_final' => ['label' => 'Quarter-finals', 'short' => 'QF'],
        'semi_final'    => ['label' => 'Semi-finals',    'short' => 'SF'],
        'final'         => ['label' => 'Final',          'short' => 'Final'],
    ];

    /**
     * Progress summary for the stage timeline + accent bar shown on every World
     * Cup page, derived entirely from wc_fixtures (no hardcoded counts). The
     * current stage is the earliest one that still has an unplayed fixture; the
     * bar / "X of Y games played" text track that stage only. `stages` lists
     * every stage that has fixture rows (so stages appear in the timeline as
     * their fixtures are added), each tagged completed / current / future. The
     * "next stage starts" date is the earliest scheduled kickoff of the
     * following stage, in Adelaide time. Cached 60s so wire:poll refreshes
     * don't re-query. Returns null when there are no fixtures at all.
     *
     * @return array{stage_label:string,current_total:int,current_completed:int,current_live:int,next_label:?string,next_start:?string,stages:array<int,array{key:string,label:string,short:string,total:int,completed:int,state:string}>}|null
     */
    public function tournamentProgress(): ?array
    {
        return Cache::remember('wc.tournament_progress', 60, function () {
            if (WcFixture::query()->doesntExist()) {
                return null;
            }

            $stageTotals = WcFixture::query()
                ->selectRaw('stage, count(*) as total')
                ->groupBy('stage')
                ->pluck('total', 'stage');

            $stageRemaining = WcFixture::query()
                ->where('status', '!=', 'completed')
                ->selectRaw('stage, count(*) as remaining')
                ->groupBy('stage')
                ->pluck('remaining', 'stage');

            $stageLive = WcFixture::query()
                ->where('status', 'live')
                ->selectRaw('stage, count(*) as live')
                ->groupBy('stage')
                ->pluck('live', 'stage');

            // Walk the stages in order: the current stage is the first one that
            // exists and still has an unplayed fixture; the next stage is the
            // following one that exists.
            $currentStage = null;
            $nextStage = null;
            foreach (array_keys(self::STAGES) as $key) {
                if (! isset($stageTotals[$key])) {
                    continue;
                }
                if ($currentStage === null) {
                    if ((int) ($stageRemaining[$key] ?? 0) > 0) {
                        $currentStage = $key;
                    }
                } else {
                    $nextStage = $key;
                    break;
                }
            }

            // Every fixture played → label by the last stage that exists.
            if ($currentStage === null) {
                foreach (array_reverse(array_keys(self::STAGES)) as $key) {
                    if (isset($stageTotals[$key])) {
                        $currentStage = $key;
                        break;
                    }
                }
            }

            // One timeline entry per stage that has fixtures, in round order. A
            // stage is "completed" once all its fixtures are played, "current"
            // for the active stage, otherwise "future".
            $stages = [];
            foreach (self::STAGES as $key => $labels) {
                if (! isset($stageTotals[$key])) {
                    continue;
                }
                $stageTotal = (int) $stageTotals[$key];
                $stageDone = $stageTotal - (int) ($stageRemaining[$key] ?? 0);
                $state = $stageDone >= $stageTotal
                    ? 'completed'
                    : ($key === $currentStage ? 'current' : 'future');

                $stages[] = [
                    'key'       => $key,
                    'label'     => $labels['label'],
                    'short'     => $labels['short'],
                    'total'     => $stageTotal,
                    'completed' => $stageDone,
                    'state'     => $state,
                ];
            }

            $currentTotal = (int) ($stageTotals[$currentStage] ?? 0);
            $currentCompleted = $currentTotal - (int) ($stageRemaining[$currentStage] ?? 0);
            $currentLive = (int) ($stageLive[$currentStage] ?? 0);

            $nextStart = null;
            if ($nextStage !== null) {
                $earliest = WcFixture::query()
                    ->where('stage', $nextStage)
                    ->min('match_datetime');
                $nextStart = $earliest
                    ? Carbon::parse($earliest)->timezone('Australia/Adelaide')->format('M j')
                    : null;
            }

            return [
                'stage_label'       => self::STAGES[$currentStage]['label'] ?? 'World Cup',
                'current_total'     => $currentTotal,
                'current_completed' => $currentCompleted,
                'current_live'      => $currentLive,
                'next_label'        => $nextStage !== null ? self::STAGES[$nextStage]['label'] : null,
                'next_start'        => $nextStart,
                'stages'            => $stages,
            ];
        });
    }

    /**
     * @return array{team_goal:int,player_goal:int}
     */
    protected function pointsKey(): array
    {
        $values = WcSetting::query()
            ->whereIn('key', ['points_team_goal', 'points_player_goal'])
            ->pluck('value', 'key');

        return [
            'team_goal' => (int) ($values['points_team_goal'] ?? 1),
            'player_goal' => (int) ($values['points_player_goal'] ?? 1),
        ];
    }

    /**
     * @return array{yellow:int,red:int}
     */
    protected function cardPointsKey(): array
    {
        $values = WcSetting::query()
            ->whereIn('key', ['points_yellow_card', 'points_red_card'])
            ->pluck('value', 'key');

        return [
            'yellow' => (int) ($values['points_yellow_card'] ?? 1),
            'red' => (int) ($values['points_red_card'] ?? 3),
        ];
    }

    /**
     * Bulk-load every entry with its teams / players resolved, plus member
     * display names. Returns the raw collections so each page can enrich them
     * with whatever points it cares about (goals vs cards).
     *
     * @return array{0:Collection,1:Collection,2:Collection,3:array}
     */
    protected function loadEntries(): array
    {
        $entries = WcEntry::query()
            ->with(['entryTeams', 'entryPlayers'])
            ->orderBy('entryID')
            ->get();

        $teamIds = $entries->flatMap(fn (WcEntry $e) => $e->entryTeams->pluck('teamID'))->unique();
        $playerIds = $entries->flatMap(fn (WcEntry $e) => $e->entryPlayers->pluck('playerID'))->unique();
        $teams = WcTeam::whereIn('teamID', $teamIds)->get()->keyBy('teamID');
        $players = WcPlayer::with('team')->whereIn('playerID', $playerIds)->get()->keyBy('playerID');

        $memberNames = MemberDirectory::labels($entries->pluck('memberID')->all());

        return [$entries, $teams, $players, $memberNames];
    }

    /**
     * One enriched row per entry (teams, players, goal points). Works before
     * the draw too — entries simply have null teams / no players / zero points.
     *
     * @param  array{team_goal:int,player_goal:int}  $points
     */
    protected function entryRow(WcEntry $entry, Collection $teams, Collection $players, Collection $teamPoints, $goalCounts, array $points, array $memberNames, ?Collection $teamGoalMap = null): array
    {
        $topTeamId = $entry->entryTeams->firstWhere('tier', 1)?->teamID;
        $bottomTeamId = $entry->entryTeams->firstWhere('tier', 2)?->teamID;

        $teamIds = array_values(array_filter([$topTeamId, $bottomTeamId]));
        $teamPts = collect($teamIds)->sum(fn ($id) => $teamPoints[$id] ?? 0);

        $playerRows = $entry->entryPlayers
            ->sortBy('slot')
            ->map(function ($ep) use ($players, $goalCounts) {
                $player = $players[$ep->playerID] ?? null;

                return [
                    'playerID' => $ep->playerID,
                    'name' => $player?->name ?? '—',
                    'flag' => $player?->team?->flag,
                    'team_id' => $player?->teamID,
                    'team_name' => $player?->team?->name,
                    'eliminated' => $player?->teamID !== null && $this->eliminatedTeamIds()->has($player->teamID),
                    'goal_count' => (int) ($goalCounts[$ep->playerID] ?? 0),
                ];
            })
            ->values();

        $playerPts = $playerRows->sum('goal_count') * $points['player_goal'];

        return [
            'entryID' => $entry->entryID,
            'entry_name' => $entry->entry_name,
            'member_name' => $memberNames[$entry->memberID] ?? $entry->entry_name,
            'draw_completed' => (bool) $entry->draw_completed,
            'top_team' => $this->teamRow($teams[$topTeamId] ?? null, $topTeamId, $teamGoalMap),
            'bottom_team' => $this->teamRow($teams[$bottomTeamId] ?? null, $bottomTeamId, $teamGoalMap),
            'players' => $playerRows->all(),
            'team_ids' => $teamIds,
            'player_ids' => $playerRows->pluck('playerID')->all(),
            'team_points' => $teamPts,
            'player_points' => $playerPts,
            'total_points' => $teamPts + $playerPts,
        ];
    }

    /**
     * Points earned by each team: one point (× points_team_goal) for every goal
     * credited to the team. Own goals ARE included — wc_goals.teamID already
     * holds the team that benefits, so grouping by teamID counts each team's
     * goals-for (matching the scoreline). Shootout goals are excluded; they
     * don't contribute to the 90+ET score that drives team points.
     *
     * @param  array{team_goal:int,player_goal:int}  $points
     */
    protected function teamPointsMap(array $points): Collection
    {
        return WcGoal::query()
            ->where('is_shootout', false)
            ->selectRaw('"teamID", count(*) as goals')
            ->groupBy('teamID')
            ->pluck('goals', 'teamID')
            ->map(fn ($goals) => (int) $goals * $points['team_goal']);
    }

    /**
     * Base team row (flag / name / group). When a teamID and bulk goal map are
     * supplied, also carries goal_count — non-own-goals scored by that team —
     * for the ⚽ badge under the team name on the ladder.
     */
    protected function teamRow(?WcTeam $team, ?int $teamId = null, ?Collection $teamGoalMap = null): ?array
    {
        if (! $team) {
            return null;
        }

        return [
            'flag' => $team->flag,
            'name' => $team->name,
            'code' => $team->code,
            'group_letter' => $team->group_letter,
            'eliminated' => $teamId !== null && $this->eliminatedTeamIds()->has($teamId),
            'goal_count' => (int) ($teamGoalMap[$teamId] ?? 0),
        ];
    }

    /**
     * Human-readable scorer line for a fixture, e.g.
     * "Jiménez (2), Giménez, Müller (pen), Sané (pen) · OG: Tau".
     * Regular goals are grouped per scorer with a count; shootout pens stay
     * inline with a (pen) suffix so the line reflects the full sequence even
     * though the pens don't award points.
     */
    protected function scorerLine(Collection $goals): string
    {
        $normal = $goals->where('is_own_goal', false)->where('is_shootout', false)
            ->groupBy('playerID')
            ->map(function ($group) {
                $name = $group->first()->player?->name ?? 'Player';
                $count = $group->count();

                return $count > 1 ? "{$name} ({$count})" : $name;
            })
            ->values();

        $shootout = $goals->where('is_shootout', true)
            ->map(fn (WcGoal $g) => ($g->player?->name ?? 'Player') . ' (pen)')
            ->values();

        $own = $goals->where('is_own_goal', true)->where('is_shootout', false)
            ->map(fn (WcGoal $g) => $g->player?->name ?? 'Player')
            ->values();

        $parts = [];
        $scorers = $normal->merge($shootout);
        if ($scorers->isNotEmpty()) {
            $parts[] = $scorers->implode(', ');
        }
        if ($own->isNotEmpty()) {
            $parts[] = 'OG: ' . $own->implode(', ');
        }

        return implode(' · ', $parts);
    }

    /**
     * Currently in-play fixtures (status = 'live'), each annotated with the
     * entries that have a stake — a team in the match, or a player on the pitch.
     * Stakes are grouped by team / player and only count fully-drawn entries.
     */
    protected function liveFixtures(): Collection
    {
        $fixtures = WcFixture::query()
            ->where('status', 'live')
            ->with(['homeTeam', 'awayTeam', 'goals.player'])
            ->orderBy('match_datetime')
            ->get();

        if ($fixtures->isEmpty()) {
            return collect();
        }

        // Every team taking part in a live fixture.
        $liveTeamIds = $fixtures
            ->flatMap(fn (WcFixture $f) => array_filter([$f->home_team_id, $f->away_team_id]))
            ->unique()
            ->values();

        // Stake data is queried straight from wc_entry_teams / wc_entry_players
        // (drawn entries only) so live stakes show regardless of the page.
        $teamStakeRows = WcEntryTeam::query()
            ->whereIn('teamID', $liveTeamIds)
            ->whereHas('entry', fn ($q) => $q->where('draw_completed', true))
            ->get(['entryID', 'teamID']);

        // Players belonging to a live team, so a fixture can be matched via the
        // player's own team even when the entry didn't draw that team directly.
        $players = WcPlayer::query()
            ->whereIn('teamID', $liveTeamIds)
            ->get(['playerID', 'teamID', 'name'])
            ->keyBy('playerID');

        $playerStakeRows = WcEntryPlayer::query()
            ->whereIn('playerID', $players->keys())
            ->whereHas('entry', fn ($q) => $q->where('draw_completed', true))
            ->get(['entryID', 'playerID']);

        $teams = WcTeam::whereIn('teamID', $liveTeamIds)->get()->keyBy('teamID');

        $entryIds = $teamStakeRows->pluck('entryID')
            ->merge($playerStakeRows->pluck('entryID'))
            ->unique()
            ->values();
        $entries = WcEntry::whereIn('entryID', $entryIds)
            ->get(['entryID', 'memberID', 'entry_name'])
            ->keyBy('entryID');
        $memberNames = MemberDirectory::labels($entries->pluck('memberID')->all());

        $nameFor = function ($entryID) use ($entries, $memberNames) {
            $entry = $entries[$entryID] ?? null;

            return $entry ? ($memberNames[$entry->memberID] ?? $entry->entry_name) : null;
        };

        return $fixtures->map(function (WcFixture $fixture) use ($teamStakeRows, $playerStakeRows, $players, $teams, $nameFor) {
            $fixtureTeamIds = array_values(array_filter([$fixture->home_team_id, $fixture->away_team_id]));

            $teamGroups = [];   // teamID   => ['team' => name, 'names' => [...]]
            $playerGroups = []; // playerID => ['player' => name, 'names' => [...]]

            foreach ($teamStakeRows as $row) {
                if (! in_array($row->teamID, $fixtureTeamIds)) {
                    continue;
                }
                $name = $nameFor($row->entryID);
                if ($name === null) {
                    continue;
                }
                $teamGroups[$row->teamID]['team'] = $teams[$row->teamID]->name ?? '';
                $teamGroups[$row->teamID]['names'][] = $name;
            }

            foreach ($playerStakeRows as $row) {
                $player = $players[$row->playerID] ?? null;
                if ($player === null || ! in_array($player->teamID, $fixtureTeamIds)) {
                    continue;
                }
                $name = $nameFor($row->entryID);
                if ($name === null) {
                    continue;
                }
                $playerGroups[$row->playerID]['player'] = $player->name;
                $playerGroups[$row->playerID]['names'][] = $name;
            }

            return [
                'group_letter' => $fixture->group_letter,
                'home_flag' => $fixture->homeTeam?->flag,
                'home_name' => $fixture->homeTeam?->name ?? $fixture->home_placeholder ?? 'TBD',
                'away_flag' => $fixture->awayTeam?->flag,
                'away_name' => $fixture->awayTeam?->name ?? $fixture->away_placeholder ?? 'TBD',
                'home_score' => $fixture->home_score ?? 0,
                'away_score' => $fixture->away_score ?? 0,
                'scorers' => $this->scorerLine($fixture->goals),
                'team_stakes' => array_values($teamGroups),
                'player_stakes' => array_values($playerGroups),
            ];
        });
    }
}
