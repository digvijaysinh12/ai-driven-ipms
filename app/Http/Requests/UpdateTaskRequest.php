<?php

namespace App\Http\Requests;

use App\Models\Task;
use App\Models\TaskType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');
        $user = $this->user();

        return $task && $user && $user->role?->name === 'mentor';
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'task_type_id' => ['sometimes', 'required', 'integer', Rule::exists('task_types', 'id')],
        ];
    }

    protected function prepareForValidation(): void
    {
        $taskTypeId = $this->input('task_type_id');

        if ($taskTypeId === null) {
            $legacyType = $this->input('task_type', $this->input('type'));

            if (filled($legacyType)) {
                $taskTypeId = TaskType::query()
                    ->where('slug', $legacyType)
                    ->value('id');
            }
        }

        if ($taskTypeId !== null) {
            $this->merge([
                'task_type_id' => $taskTypeId,
            ]);
        }
    }
}
