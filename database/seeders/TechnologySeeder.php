<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Technology;

class TechnologySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Technology::firstOrCreate(['name' => 'PHP']);
        Technology::firstOrCreate(['name' => 'Java']);
        Technology::firstOrCreate(['name' => 'QA']);
        Technology::firstOrCreate(['name' => 'AI']);
        Technology::firstOrCreate(['name' => 'MERN']);
    }
}
