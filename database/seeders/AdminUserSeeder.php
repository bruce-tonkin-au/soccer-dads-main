<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->upsert(
            [
                'name'       => 'Bruce Tonkin',
                'email'      => 'bruce@codesnap.com.au',
                'password'   => Hash::make('SoccerDads2024!'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            ['email'],
            ['name', 'updated_at']
        );
    }
}
