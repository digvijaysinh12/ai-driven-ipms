<?php

namespace Database\Seeders;

use App\Models\TaskType;
use Illuminate\Database\Seeder;

class TaskTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [

            [
                'name' => 'Multiple Choice',
                'slug' => 'mcq',
                'icon' => 'list-checks',
                'description' => 'Automated quiz with multiple options.',
            ],

            [
                'name' => 'True / False',
                'slug' => 'true_false',
                'icon' => 'check-circle',
                'description' => 'Binary choice questions.',
            ],

            [
                'name' => 'Fill in the Blank',
                'slug' => 'blank',
                'icon' => 'input-cursor-text',
                'description' => 'Short answer questions.',
            ],

            [
                'name' => 'Descriptive Theory',
                'slug' => 'descriptive',
                'icon' => 'file-text',
                'description' => 'Long answer questions.',
            ],

            [
                'name' => 'Coding Problem',
                'slug' => 'coding',
                'icon' => 'code',
                'description' => 'Solve coding problems (like LeetCode).',
            ],

            [
                'name' => 'File Upload',
                'slug' => 'file',
                'icon' => 'upload-cloud',
                'description' => 'Upload documents like PDF or ZIP.',
            ],

            [
                'name' => 'GitHub Repository',
                'slug' => 'github',
                'icon' => 'github',
                'description' => 'Submit GitHub repository link.',
            ],

        ];

        foreach ($types as $type) {
            TaskType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}