<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddCommentatorDefault extends Command
{
    protected $signature = 'commentators:add-default-column';
    protected $description = 'Add commentatorDefault column to commentators table and set ID 6 as default';

    public function handle()
    {
        $exists = DB::select(
            "SELECT column_name FROM information_schema.columns WHERE table_name = 'commentators' AND column_name = 'commentatorDefault'"
        );

        if ($exists) {
            $this->info('Column commentatorDefault already exists — skipping ALTER TABLE.');
        } else {
            DB::statement('ALTER TABLE commentators ADD COLUMN "commentatorDefault" integer DEFAULT 0');
            $this->info('Column commentatorDefault added successfully.');
        }

        $affected = DB::update('UPDATE commentators SET "commentatorDefault" = 1 WHERE "commentatorID" = 6');

        if ($affected) {
            $this->info('commentatorID 6 set as default.');
        } else {
            $this->warn('No row with commentatorID = 6 found — default not set. You can set it via the admin UI.');
        }

        $this->info('Done.');
        return Command::SUCCESS;
    }
}
