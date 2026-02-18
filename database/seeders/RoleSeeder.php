<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'name' => 'hr',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'mentor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'intern',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
