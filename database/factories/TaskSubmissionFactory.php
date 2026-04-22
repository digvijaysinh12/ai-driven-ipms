<?php

namespace Database\Factories;

use App\Enums\SubmissionStatus;
use App\Models\Task;
use App\Models\TaskSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskSubmissionFactory extends Factory
{
    protected $model = TaskSubmission::class;

    public function configure(): static
    {
        return $this
            ->afterMaking(function (TaskSubmission $submission): void {
                $submission->user_id ??= $submission->task?->assigned_to;
            })
            ->afterCreating(function (TaskSubmission $submission): void {
                if ($submission->user_id === null && $submission->task?->assigned_to !== null) {
                    $submission->forceFill([
                        'user_id' => $submission->task->assigned_to,
                    ])->save();
                }
            });
    }

    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'user_id' => null,
            'answers' => ['sample' => fake()->sentence()],
            'status' => SubmissionStatus::Submitted,
            'score' => null,
            'feedback' => null,
            'submitted_at' => now(),
            'reviewed_at' => null,
            'reviewed_by' => null,
        ];
    }
}
