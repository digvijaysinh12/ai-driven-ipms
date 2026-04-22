<?php

namespace Database\Seeders;

use App\Models\SubmissionStatus;
use App\Models\TaskType;
use Illuminate\Database\Seeder;

class DatabaseRefactorSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Task Types
        $taskTypes = [
            ['name' => 'Multiple Choice Question', 'slug' => 'mcq', 'icon' => 'list-bullet'],
            ['name' => 'Descriptive / Theory', 'slug' => 'descriptive', 'icon' => 'align-left'],
            ['name' => 'File Upload', 'slug' => 'file', 'icon' => 'arrow-up-tray'],
            ['name' => 'GitHub Repository', 'slug' => 'github', 'icon' => 'code-bracket'],
        ];

        foreach ($taskTypes as $type) {
            TaskType::updateOrCreate(['slug' => $type['slug']], $type);
        }

        // Seed Submission Statuses
        $statuses = [
            ['name' => 'Draft', 'slug' => 'draft', 'color' => '#6B7280'], // Gray
            ['name' => 'Submitted', 'slug' => 'submitted', 'color' => '#3B82F6'], // Blue
            ['name' => 'Under AI Evaluation', 'slug' => 'ai_evaluating', 'color' => '#8B5CF6'], // Purple
            ['name' => 'AI Evaluated', 'slug' => 'ai_evaluated', 'color' => '#10B981'], // Green
            ['name' => 'Under Mentor Review', 'slug' => 'reviewing', 'color' => '#F59E0B'], // Amber
            ['name' => 'Completed', 'slug' => 'completed', 'color' => '#059669'], // Dark Green
            ['name' => 'Needs Revision', 'slug' => 'revision', 'color' => '#EF4444'], // Red
        ];

        foreach ($statuses as $status) {
            SubmissionStatus::updateOrCreate(['slug' => $status['slug']], $status);
        }
    }
}
