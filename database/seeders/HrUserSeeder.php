<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; 
use App\Models\Role;
use App\Models\User;

class HrUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Check if the role exists
        $hrRole = Role::where('name', 'hr')->first();

        if (!$hrRole) {
            // Throwing an exception is good for debugging!
            throw new \Exception('HR role not found. Please run RoleSeeder first.');
        }

        // 2. Create the user if they don't exist
        User::firstOrCreate(
            ['email' => 'hr@role.com'], // Unique identifier
            [
                'name' => 'HR Admin',
                'password' => Hash::make('hr@123'),
                'role_id' => $hrRole->id,
                'status' => 'approved',
                'email_verified_at' => now(),
            ]
        ); 
    }
}