<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Idempotent: safe to run multiple times. Uses firstOrCreate to avoid
     * duplicate key violations on unique email constraint.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@agriops.test'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        User::firstOrCreate(
            ['email' => 'worker@agriops.test'],
            [
                'name' => 'Worker User',
                'password' => Hash::make('password'),
                'role' => User::ROLE_WORKER,
            ]
        );
    }
}
