<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Blocked = 'blocked';
    case Deactivated = 'deactivated';
}
