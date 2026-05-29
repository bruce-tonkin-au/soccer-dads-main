<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupGuestCarts extends Command
{
    protected $signature = 'store:cleanup-guest-carts';
    protected $description = 'Delete expired guest_carts rows (cart_token cookie outlived its session)';

    public function handle(): int
    {
        if (!Schema::hasTable('guest_carts')) {
            $this->warn('guest_carts table does not exist — run migrations first.');
            return self::FAILURE;
        }

        $deleted = DB::table('guest_carts')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        $this->info("Deleted {$deleted} expired guest cart row(s).");
        return self::SUCCESS;
    }
}
