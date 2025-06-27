<?php

namespace App\Enums;

enum UserRole: string
{
    case RECRUITER = 'recruiter';
    case CANDIDATE = 'candidate';
}
