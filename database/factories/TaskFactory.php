<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Task;
use App\Models\TaskType as TaskTypeModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $mentorRole = Role::firstOrCreate(['name' => 'mentor']);

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'task_type_id' => TaskTypeModel::firstOrCreate(
                ['slug' => 'descriptive'],
                ['name' => 'Descriptive']
            )->id,
            'created_by' => User::factory()->state([
                'role_id' => $mentorRole->id,
                'status' => 'approved',
            ]),
        ];
    }
}
