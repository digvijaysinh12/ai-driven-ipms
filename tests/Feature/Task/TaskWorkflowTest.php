<?php

namespace Tests\Feature\Task;

use App\Models\MentorAssignment;
use App\Models\Task;
use App\Models\TaskQuestion;
use App\Models\TaskSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_mentor_can_create_a_task_for_an_assigned_intern(): void
    {
        $mentor = User::factory()->mentor()->create();
        $intern = User::factory()->intern()->create();

        MentorAssignment::create([
            'intern_id' => $intern->id,
            'mentor_id' => $mentor->id,
            'assigned_by' => $mentor->id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($mentor)->post(route('mentor.tasks.store'), [
            'title' => 'Laravel API Task',
            'description' => 'Build a clean internship API endpoint.',
            'task_type' => 'descriptive',
            'assigned_to' => $intern->id,
        ]);

        $task = Task::first();

        $response->assertRedirect(route('mentor.tasks.show', $task));

        $this->assertDatabaseHas('tasks', [
            'title' => 'Laravel API Task',
            'task_type' => 'descriptive',
            'created_by' => $mentor->id,
            'assigned_to' => $intern->id,
        ]);
    }

    public function test_intern_can_submit_an_assigned_task(): void
    {
        $mentor = User::factory()->mentor()->create();
        $intern = User::factory()->intern()->create();

        MentorAssignment::create([
            'intern_id' => $intern->id,
            'mentor_id' => $mentor->id,
            'assigned_by' => $mentor->id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        $task = Task::create([
            'title' => 'Explain service containers',
            'description' => 'Answer the architectural questions.',
            'task_type' => 'descriptive',
            'created_by' => $mentor->id,
            'assigned_to' => $intern->id,
        ]);

        TaskQuestion::create([
            'task_id' => $task->id,
            'question' => 'What problem does dependency injection solve?',
        ]);

        $response = $this->actingAs($intern)->post(route('intern.tasks.submit', $task), [
            'answers' => [
                $task->questions()->first()->id => 'It reduces coupling and improves testability.',
            ],
        ]);

        $response->assertRedirect(route('intern.submissions'));

        $this->assertDatabaseHas('task_submissions', [
            'task_id' => $task->id,
            'user_id' => $intern->id,
            'status' => 'submitted',
        ]);
    }

    public function test_mentor_can_review_a_submission(): void
    {
        $mentor = User::factory()->mentor()->create();
        $intern = User::factory()->intern()->create();

        $task = Task::create([
            'title' => 'Review architecture notes',
            'description' => 'Inspect the answers and score them.',
            'task_type' => 'descriptive',
            'created_by' => $mentor->id,
            'assigned_to' => $intern->id,
        ]);

        $submission = TaskSubmission::create([
            'task_id' => $task->id,
            'user_id' => $intern->id,
            'answers' => ['notes' => 'A solid explanation'],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($mentor)->post(route('mentor.submissions.review', $submission), [
            'score' => 88,
            'feedback' => 'Strong technical reasoning with clear structure.',
        ]);

        $response->assertRedirect(route('mentor.submissions.show', $submission));

        $this->assertDatabaseHas('task_submissions', [
            'id' => $submission->id,
            'status' => 'reviewed',
            'score' => 88,
            'reviewed_by' => $mentor->id,
        ]);
    }

    public function test_duplicate_submission_is_blocked(): void
    {
        $mentor = User::factory()->mentor()->create();
        $intern = User::factory()->intern()->create();

        MentorAssignment::create([
            'intern_id' => $intern->id,
            'mentor_id' => $mentor->id,
            'assigned_by' => $mentor->id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        $task = Task::create([
            'title' => 'Duplicate guard task',
            'description' => 'This task should only accept one submission.',
            'task_type' => 'github',
            'created_by' => $mentor->id,
            'assigned_to' => $intern->id,
        ]);

        TaskSubmission::create([
            'task_id' => $task->id,
            'user_id' => $intern->id,
            'answers' => ['repository_url' => 'https://github.com/example/repo'],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this
            ->actingAs($intern)
            ->from(route('intern.tasks.show', $task))
            ->post(route('intern.tasks.submit', $task), [
                'repository_url' => 'https://github.com/example/repo',
            ]);

        $response->assertRedirect(route('intern.tasks.show', $task));
        $response->assertSessionHasErrors('task');
        $this->assertDatabaseCount('task_submissions', 1);
    }
}
