<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillBibsMemberID extends Command
{
    protected $signature   = 'bibs:backfill {--dry-run : Preview changes without writing to the database}';
    protected $description = 'Backfill gameBibsMemberID from the legacy gameBibs code column';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('[DRY RUN] No changes will be written.');
        }

        // ---------------------------------------------------------------
        // 1. Load all members
        // ---------------------------------------------------------------
        $members = DB::table('members')->get();

        // Index by slug, full name (first last), and legacy code (memberCode)
        $bySlug = $members->keyBy('memberSlug');
        $byCode = $members->keyBy('memberCode');

        // Name maps (case-insensitive)
        $byName = [];
        foreach ($members as $m) {
            $key = strtolower(trim("{$m->memberNameFirst} {$m->memberNameLast}"));
            $byName[$key] = $m;
            // Also index Last, First in case data was stored that way
            $key2 = strtolower(trim("{$m->memberNameLast} {$m->memberNameFirst}"));
            $byName[$key2] = $m;
        }

        // ---------------------------------------------------------------
        // 2. Build a legacy-code → memberID inference map using results.
        //
        //    The old gameCaptain1/2/3 fields store the same 12-char codes.
        //    The results table records which members played on which team for
        //    each game.  A captain code uniquely identifies a player within a
        //    game/team pair.  We collect all (code, memberID) co-occurrences
        //    and use majority vote to infer the mapping.
        // ---------------------------------------------------------------
        $this->info('Building legacy-code → memberID map from results data…');

        $teams = DB::table('teams')->get()->keyBy('teamKey');

        $games = DB::table('games')
            ->whereNotNull('gameBibs')
            ->where('gameBibs', '!=', '')
            ->get(['gameID', 'gameBibs',
                   'gameCaptain1', 'gameTeam1',
                   'gameCaptain2', 'gameTeam2',
                   'gameCaptain3', 'gameTeam3']);

        // For each game, map captain-code → set of memberIDs who played on that team
        $codeVotes = []; // code => [memberID => count]

        $results = DB::table('results')
            ->where('resultActive', 1)
            ->get(['resultGameID', 'resultMemberID', 'resultTeamID'])
            ->groupBy('resultGameID');

        foreach ($games as $game) {
            $gameResults = $results[$game->gameID] ?? collect();

            $captainPairs = [
                [$game->gameCaptain1, $game->gameTeam1],
                [$game->gameCaptain2, $game->gameTeam2],
                [$game->gameCaptain3, $game->gameTeam3],
            ];

            foreach ($captainPairs as [$captainCode, $teamKey]) {
                if (!$captainCode || !$teamKey) {
                    continue;
                }
                $team = $teams[$teamKey] ?? null;
                if (!$team) {
                    continue;
                }
                // Members who played on this team in this game
                $teamMembers = $gameResults->where('resultTeamID', $team->teamID)->pluck('resultMemberID');
                foreach ($teamMembers as $mid) {
                    $codeVotes[$captainCode][$mid] = ($codeVotes[$captainCode][$mid] ?? 0) + 1;
                }
            }
        }

        // Resolve: pick the memberID with the most votes; require ≥60% confidence
        $legacyMap = []; // code => memberID
        foreach ($codeVotes as $code => $votes) {
            $total = array_sum($votes);
            arsort($votes);
            $topMid   = array_key_first($votes);
            $topCount = $votes[$topMid];
            if ($topCount / $total >= 0.60) {
                $legacyMap[$code] = $topMid;
            }
        }

        $this->info(sprintf('  Legacy codes with confident member mapping: %d', count($legacyMap)));

        // ---------------------------------------------------------------
        // 3. Process each game that needs backfilling
        // ---------------------------------------------------------------
        $gamesToUpdate = DB::table('games')
            ->whereNotNull('gameBibs')
            ->where('gameBibs', '!=', '')
            ->whereNull('gameBibsMemberID')
            ->get(['gameID', 'gameBibs']);

        $this->info(sprintf('Games needing gameBibsMemberID: %d', $gamesToUpdate->count()));

        $matched   = 0;
        $unmatched = 0;
        $unmatched_list = [];

        $memberCache = [];

        foreach ($gamesToUpdate as $game) {
            $raw   = trim($game->gameBibs);
            $found = null;

            // Strategy 1: direct name lookup (case-insensitive)
            $found = $byName[strtolower($raw)] ?? null;

            // Strategy 2: slug lookup
            if (!$found) {
                $found = $bySlug[$raw] ?? null;
            }

            // Strategy 3: memberCode lookup
            if (!$found) {
                $found = $byCode[$raw] ?? null;
            }

            // Strategy 4: legacy-code inference
            if (!$found && isset($legacyMap[$raw])) {
                $mid = $legacyMap[$raw];
                if (!isset($memberCache[$mid])) {
                    $memberCache[$mid] = DB::table('members')->where('memberID', $mid)->first();
                }
                $found = $memberCache[$mid];
            }

            if ($found) {
                $matched++;
                $this->line(sprintf(
                    '  [MATCH]   gameID=%-4d  %-14s → %s %s (ID %d)',
                    $game->gameID, $raw,
                    $found->memberNameFirst, $found->memberNameLast, $found->memberID
                ));
                if (!$dryRun) {
                    DB::table('games')->where('gameID', $game->gameID)->update([
                        'gameBibsMemberID' => $found->memberID,
                    ]);
                }
            } else {
                $unmatched++;
                $unmatched_list[$raw] = ($unmatched_list[$raw] ?? 0) + 1;
                $this->line(sprintf(
                    '  [NO MATCH] gameID=%-4d  %s',
                    $game->gameID, $raw
                ));
            }
        }

        // ---------------------------------------------------------------
        // 4. Summary
        // ---------------------------------------------------------------
        $this->newLine();
        $this->info('─────────────────────────────────────────────');
        $this->info(sprintf('  Matched:   %d', $matched));
        $this->info(sprintf('  Unmatched: %d', $unmatched));
        $this->info('─────────────────────────────────────────────');

        if ($unmatched_list) {
            $this->newLine();
            $this->warn('Unmatched gameBibs values (needs manual assignment):');
            arsort($unmatched_list);
            foreach ($unmatched_list as $code => $count) {
                $this->line(sprintf('  %-16s  (%d game%s)', $code, $count, $count === 1 ? '' : 's'));
            }
            $this->newLine();
            $this->warn('For each unmatched code, find the player and run:');
            $this->line('  UPDATE games SET gameBibsMemberID = <memberID>');
            $this->line('  WHERE gameBibs = \'<code>\' AND gameBibsMemberID IS NULL;');
            $this->newLine();
            $this->warn('NOTE: gameBibs stores legacy 12-char player codes from the old');
            $this->warn('tracking system. There is no automatic name mapping for these.');
            $this->warn('Use the admin panel (Edit Game) to assign bibs washers manually.');
        }

        if ($dryRun) {
            $this->newLine();
            $this->info('[DRY RUN] No changes were written. Run without --dry-run to apply.');
        }

        return Command::SUCCESS;
    }
}
