<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixActionTimesUtcToAdelaide extends Command
{
    protected $signature   = 'actions:fix-utc {gameID} {--dry-run : Preview without writing}';
    protected $description = 'Add 9h30m to scoring-actions.actionTime for a game night stored in UTC instead of Adelaide local';

    public function handle(): int
    {
        $gameID = (int) $this->argument('gameID');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('[DRY RUN] No changes will be written.');
        }

        $scoringIDs = DB::table('scoring')->where('gameID', $gameID)->pluck('scoringID');

        if ($scoringIDs->isEmpty()) {
            $this->error("No scoring rows found for gameID={$gameID}.");
            return Command::FAILURE;
        }

        $this->info("Scoring rows: {$scoringIDs->count()} (IDs: {$scoringIDs->implode(', ')})");

        $count = DB::table('scoring-actions')
            ->whereIn('scoringID', $scoringIDs)
            ->where('actionActive', 1)
            ->count();

        $this->info("Active scoring-actions to update: {$count}");

        if ($count === 0) {
            $this->warn('No active actions found.');
            return Command::SUCCESS;
        }

        $samples = DB::table('scoring-actions')
            ->whereIn('scoringID', $scoringIDs)
            ->where('actionActive', 1)
            ->orderBy('actionID')
            ->limit(5)
            ->pluck('actionTime', 'actionID');

        $this->info('Sample BEFORE:');
        foreach ($samples as $id => $t) {
            $this->line("  actionID={$id}  actionTime={$t}");
        }

        if (!$dryRun) {
            DB::statement(
                'UPDATE "scoring-actions"
                 SET "actionTime"    = "actionTime"    + INTERVAL \'570 minutes\',
                     "actionCreated" = "actionCreated" + INTERVAL \'570 minutes\',
                     "actionEdited"  = "actionEdited"  + INTERVAL \'570 minutes\'
                 WHERE "scoringID" IN (' . $scoringIDs->implode(',') . ')
                   AND "actionActive" = 1'
            );

            $samplesAfter = DB::table('scoring-actions')
                ->whereIn('scoringID', $scoringIDs)
                ->where('actionActive', 1)
                ->orderBy('actionID')
                ->limit(5)
                ->pluck('actionTime', 'actionID');

            $this->info('Sample AFTER:');
            foreach ($samplesAfter as $id => $t) {
                $this->line("  actionID={$id}  actionTime={$t}");
            }

            $this->info("Done. {$count} action(s) updated for gameID={$gameID}.");
        } else {
            $this->info('[DRY RUN] Run without --dry-run to apply the +9h30m conversion.');
        }

        return Command::SUCCESS;
    }
}
