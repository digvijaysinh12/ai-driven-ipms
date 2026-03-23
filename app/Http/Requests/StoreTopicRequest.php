<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role->name === 'mentor';
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'mcq_count' => 'nullable|integer|min:0|max:50',
            'blank_count' => 'nullable|integer|min:0|max:50',
            'true_false_count' => 'nullable|integer|min:0|max:50',
            'output_count' => 'nullable|integer|min:0|max:50',
            'coding_count' => 'nullable|integer|min:0|max:50',
        ];
    }
}