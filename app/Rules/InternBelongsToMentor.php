<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class InternBelongsToMentor implements ValidationRule
{
    public function __construct(private readonly ?int $mentorId)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->mentorId === null) {
            $fail('Unable to validate the selected intern.');

            return;
        }

        $assigned = DB::table('mentor_assignments')
            ->where('mentor_id', $this->mentorId)
            ->where('intern_id', $value)
            ->where('is_active', 1)
            ->exists();

        if (! $assigned) {
            $fail('The selected intern is not assigned to you.');
        }
    }
}
