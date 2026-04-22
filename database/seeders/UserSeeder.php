<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $internRole = Role::where('name', 'intern')->firstOrFail();
        $mentorRole = Role::where('name', 'mentor')->firstOrFail();
        $hrRole = Role::where('name', 'hr')->first();

        $techIds = Technology::pluck('id')->toArray();

        // INTERNS
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => "Intern $i",
                'email' => "intern$i@test.com",
                'password' => Hash::make('Password@123'),
                'role_id' => $internRole->id,
                'technology_id' => $techIds[array_rand($techIds)],
                'status' => 'approved', // ✅ REQUIRED
                'email_verified_at' => now(), // ✅ REQUIRED
            ]);
        }

        // MENTORS
        for ($i = 1; $i <= 3; $i++) {aaqqqq
            User::create([
                'name' => "Mentor $i",
                'email' => "mentor$i@test.com",
                'password' => Hash::make('Password@123'),
                'role_id' => $mentorRole->id,
                'status' => 'approved', // ✅ REQUIRED
                'email_verified_at' => now(), // ✅ REQUIRED
            ]);
        }

    }
}
