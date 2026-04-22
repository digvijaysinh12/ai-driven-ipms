<?php

namespace Database\Seeders;

use App\Models\SubmissionStatus;
use Illuminate\Database\Seeder;

class SubmissionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Pending',
                'slug' => 'pending',
                'color' => 'gray',
            ],
            [
                'name' => 'In Progress',
                'slug' => 'in_progress',
                'color' => 'blue',
            ],
            [
                'name' => 'Submitted',
                'slug' => 'submitted',
                'color' => 'indigo',
            ],
            [
                'name' => 'AI Evaluating',
                'slug' => 'ai_evaluating',
                'color' => 'amber',
            ],
            [
                'name' => 'AI Evaluated',
                'slug' => 'ai_evaluated',
                'color' => 'purple',
            ],
            [
                'name' => 'Completed',
                'slug' => 'completed',
                'color' => 'emerald',
            ],
        ];

        foreach ($statuses as $status) {
            SubmissionStatus::updateOrCreate(['slug' => $status['slug']], $status);
        }
    }
}
