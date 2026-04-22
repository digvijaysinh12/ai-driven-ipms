<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $task = $this->route('task');

        return $user 
            && $user->role?->name === 'mentor'
            && $task 
            && $task->created_by === $user->id;
    }

    public function rules(): array
    {
        return [
            'intern_ids' => 'required|array',
            'intern_ids.*' => 'exists:users,id',
            'due_at' => 'nullable|date',
        ];
    }
}