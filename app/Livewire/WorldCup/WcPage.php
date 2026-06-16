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
use Illuminate\Support\Collection;
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
    protected function entryRow(WcEntry $entry, Collection $teams, Collection $players, Collection $teamPoints, $goalCounts, array $points, array $memberNames): array
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
            'top_team' => $this->teamRow($teams[$topTeamId] ?? null),
            'bottom_team' => $this->teamRow($teams[$bottomTeamId] ?? null),
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
     * goals-for (matching the scoreline).
     *
     * @param  array{team_goal:int,player_goal:int}  $points
     */
    protected function teamPointsMap(array $points): Collection
    {
        return WcGoal::query()
            ->selectRaw('"teamID", count(*) as goals')
            ->groupBy('teamID')
            ->pluck('goals', 'teamID')
            ->map(fn ($goals) => (int) $goals * $points['team_goal']);
    }

    protected function teamRow(?WcTeam $team): ?array
    {
        if (! $team) {
            return null;
        }

        return [
            'flag' => $team->flag,
            'name' => $team->name,
            'code' => $team->code,
            'group_letter' => $team->group_letter,
        ];
    }

    /**
     * Human-readable scorer line for a fixture, e.g. "Jiménez (2), Giménez · OG: Tau".
     */
    protected function scorerLine(Collection $goals): string
    {
        $normal = $goals->where('is_own_goal', false)
            ->groupBy('playerID')
            ->map(function ($group) {
                $name = $group->first()->player?->name ?? 'Player';
                $count = $group->count();

                return $count > 1 ? "{$name} ({$count})" : $name;
            })
            ->values();

        $own = $goals->where('is_own_goal', true)
            ->map(fn (WcGoal $g) => $g->player?->name ?? 'Player')
            ->values();

        $parts = [];
        if ($normal->isNotEmpty()) {
            $parts[] = $normal->implode(', ');
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
