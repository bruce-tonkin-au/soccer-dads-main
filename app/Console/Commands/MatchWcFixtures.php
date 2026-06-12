<?php

namespace App\Console\Commands;

use App\Models\WcFixture;
use App\Models\WcTeam;
use App\Support\ApiFootball;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MatchWcFixtures extends Command
{
    protected $signature = 'wc:match-fixtures';

    protected $description = 'Match our wc_fixtures rows to API-Football fixture ids by team names + kick-off time';

    /** Kick-off times must agree within this window (seconds) to count as a match. */
    private const TIME_WINDOW = 2 * 60 * 60;

    /**
     * Known name differences between API-Football and our wc_teams names.
     * Keys and the canonical target are both normalised (lower, no accents).
     */
    private const TEAM_ALIASES = [
        'korea republic'                => 'south korea',
        'republic of korea'             => 'south korea',
        'usa'                           => 'united states',
        'united states of america'      => 'united states',
        'czech republic'                => 'czechia',
        'bosnia'                        => 'bosnia and herzegovina',
        'bosnia & herzegovina'          => 'bosnia and herzegovina',
        'congo dr'                      => 'dr congo',
        'congo-kinshasa'                => 'dr congo',
        'democratic republic of congo'  => 'dr congo',
        'dr congo'                      => 'dr congo',
        'cabo verde'                    => 'cape verde',
        "cote d'ivoire"                 => 'ivory coast',
        'cote divoire'                  => 'ivory coast',
        'turkiye'                       => 'turkey',
    ];

    public function handle(ApiFootball $api): int
    {
        $this->info('Fetching World Cup fixtures from API-Football…');

        try {
            $apiFixtures = $api->worldCupFixtures();
        } catch (\Throwable $e) {
            $this->error('API request failed: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->line('API returned ' . count($apiFixtures) . ' fixture(s).');

        // Normalised team-name => teamID lookup.
        $teamsByName = WcTeam::all(['teamID', 'name'])
            ->mapWithKeys(fn (WcTeam $t) => [$this->normalizeTeam($t->name) => $t->teamID]);

        $ourFixtures = WcFixture::all();

        $matched = 0;
        $alreadySet = 0;
        $unmatched = [];

        foreach ($apiFixtures as $api) {
            $apiId    = $api['fixture']['id'] ?? null;
            $homeName = $api['teams']['home']['name'] ?? null;
            $awayName = $api['teams']['away']['name'] ?? null;
            $kickoff  = isset($api['fixture']['date']) ? Carbon::parse($api['fixture']['date'])->utc() : null;

            $label = trim(($homeName ?? '?') . ' v ' . ($awayName ?? '?'))
                . ($kickoff ? ' @ ' . $kickoff->format('Y-m-d H:i') . ' UTC' : '');

            if (! $apiId || ! $homeName || ! $awayName || ! $kickoff) {
                $unmatched[] = $label . ' (incomplete API data)';
                continue;
            }

            $homeTeamId = $teamsByName[$this->normalizeTeam($homeName)] ?? null;
            $awayTeamId = $teamsByName[$this->normalizeTeam($awayName)] ?? null;

            if (! $homeTeamId || ! $awayTeamId) {
                $unmatched[] = $label . ' (team name not recognised)';
                continue;
            }

            // Our fixture for this pairing, kick-off within the safety window.
            $ours = $ourFixtures->first(function (WcFixture $f) use ($homeTeamId, $awayTeamId, $kickoff) {
                return (int) $f->home_team_id === $homeTeamId
                    && (int) $f->away_team_id === $awayTeamId
                    && $f->match_datetime !== null
                    && abs($f->match_datetime->getTimestamp() - $kickoff->getTimestamp()) <= self::TIME_WINDOW;
            });

            if (! $ours) {
                $unmatched[] = $label . ' (no matching wc_fixtures row)';
                continue;
            }

            if ((int) $ours->api_football_id === (int) $apiId) {
                $alreadySet++;
                continue;
            }

            $ours->api_football_id = $apiId;
            $ours->save();
            $matched++;
        }

        $this->newLine();
        $this->info("Matched {$matched} new fixture(s); {$alreadySet} already linked.");

        if (! empty($unmatched)) {
            $this->warn(count($unmatched) . ' unmatched API fixture(s):');
            foreach ($unmatched as $line) {
                $this->line('  • ' . $line);
            }
        } else {
            $this->info('All API fixtures matched.');
        }

        return self::SUCCESS;
    }

    /**
     * Lower-case, strip accents, collapse whitespace, then apply known aliases
     * so both API and DB names converge on a single canonical form.
     */
    private function normalizeTeam(string $name): string
    {
        $n = Str::of($name)->ascii()->lower()->squish()->value();

        return self::TEAM_ALIASES[$n] ?? $n;
    }
}
