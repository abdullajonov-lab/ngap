<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Enums;

enum BroadcastStatus: string
{
    case Pending = 'pending';
    case Sending = 'sending';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Failed = 'failed';
}
