<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user exists
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            User::create([
                'username' => 'admin',
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'balance' => 0,
            ]);
            $this->command->info('Admin user created successfully.');
        } else {
            // Force update credentials
            $admin->email = 'admin@example.com';
            $admin->password = Hash::make('password');
            $admin->save();
            $this->command->info('Admin user credentials updated.');
        }
        
        $this->command->info('Username: admin');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Password: password');
    }
}
