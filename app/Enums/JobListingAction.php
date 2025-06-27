<?php

namespace App\Enums;

enum JobListingAction: string
{
    case ACTIVATE = 'activate';
    case DEACTIVATE = 'deactivate';
    case PAUSE = 'pause';
    case RESUME = 'resume';
}
