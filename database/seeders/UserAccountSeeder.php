<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hrRole = Role::where('name', 'hr')->first();
        $mentorRole = Role::where('name', 'mentor')->first();
        $internRole = Role::where('name', 'intern')->first();
        $techs = Technology::all();

        // 1 HR (Approved)
        User::updateOrCreate(
            ['email' => 'hr@system.com'],
            [
                'name' => 'HR Administrator',
                'password' => Hash::make('password'),
                'role_id' => $hrRole->id,
                'status' => 'approved',
                'email_verified_at' => now(),
            ]
        );

        // 3 Mentors (Pending)
        for ($i = 1; $i <= 3; $i++) {
            User::updateOrCreate(
                ['email' => "mentor{$i}@system.com"],
                [
                    'name' => "Mentor User {$i}",
                    'password' => Hash::make('password'),
                    'role_id' => $mentorRole->id,
                    'status' => 'pending',
                    'email_verified_at' => now(),
                ]
            );
        }

        // 10 Interns (Pending)
        for ($i = 1; $i <= 10; $i++) {
            User::updateOrCreate(
                ['email' => "intern{$i}@system.com"],
                [
                    'name' => "Intern User {$i}",
                    'password' => Hash::make('password'),
                    'role_id' => $internRole->id,
                    'status' => 'pending',
                    'technology_id' => $techs->random()->id,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
