<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['username' => 'planner'],
            [
                'name' => 'Planner',
                'email' => 'planner@example.com',
                'password' => Hash::make('123Password@'),
            ]
        );

        User::firstOrCreate(
            ['username' => 'amir'],
            [
                'name' => 'Admin',
                'email' => 'amir@example.com',
                'password' => Hash::make('Dayang@123'),
            ]
        );
    }
}
