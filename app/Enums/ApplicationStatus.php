<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case PENDING = 'pending';
    case REVIEWED = 'reviewed';
    case INTERVIEWING = 'interviewing';
    case REJECTED = 'rejected';
    case ACCEPTED = 'accepted';
    case WITHDRAWN = 'withdrawn';
}
