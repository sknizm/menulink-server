<?php

namespace App\Enums;

enum MembershipStatus: string {
    case ACTIVE = 'ACTIVE';
    case CANCELED = 'CANCELED';
    case EXPIRED = 'EXPIRED';
    case PAUSED = 'PAUSED';
}
