<?php

namespace App\Enums;

enum TaskType: string
{
    case Mcq = 'mcq';
    case Descriptive = 'descriptive';
    case File = 'file';
    case Github = 'github';

    public function supportsQuestions(): bool
    {
        return match ($this) {
            self::Mcq, self::Descriptive => true,
            self::File, self::Github => false,
        };
    }
}
