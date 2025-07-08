<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        // Create default admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@olt.local',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
        ]);

        // Create default operator user
        User::create([
            'name' => 'Operator',
            'email' => 'operator@olt.local',
            'password' => Hash::make('operator123'),
            'email_verified_at' => now(),
        ]);
    }
}
