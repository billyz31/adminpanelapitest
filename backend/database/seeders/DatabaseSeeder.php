<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 建立管理員帳號
        if (!User::where('username', 'admin')->exists()) {
            User::create([
                'username' => 'admin',
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'balance' => 999999,
            ]);
        }

        // 建立測試玩家
        if (!User::where('username', 'player1')->exists()) {
            User::create([
                'username' => 'player1',
                'name' => 'Test Player 1',
                'password' => bcrypt('password'),
                'role' => 'player',
                'balance' => 1000,
            ]);
        }
    }
}
