<?php

namespace App\Http\Requests;

use App\Models\TaskType;
use App\Rules\InternBelongsToMentor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        return $user->role && $user->role->name === 'mentor';
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],

            'task_type_id' => [
                'required',
                'integer',
                Rule::exists('task_types', 'id')
            ],

            'difficulty' => ['required', 'in:easy,medium,hard'],
            'language' => ['nullable', 'string', 'max:50'],

            'use_ai' => ['nullable', 'boolean'],
            'question_count' => ['nullable', 'integer', 'min:1', 'max:40'],

            'intern_ids' => ['nullable', 'array', 'min:1'],
            'intern_ids.*' => [
                'integer',
                new InternBelongsToMentor($this->user()?->id)
            ],
        ];
    }
}