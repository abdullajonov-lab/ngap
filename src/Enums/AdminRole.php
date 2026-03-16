<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Enums;

enum AdminRole: string
{
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';
}
