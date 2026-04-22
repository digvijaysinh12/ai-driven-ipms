<?php

namespace App\Enums;

enum UserRole: string
{
    case HR = 'hr';
    case Mentor = 'mentor';
    case Intern = 'intern';
}
