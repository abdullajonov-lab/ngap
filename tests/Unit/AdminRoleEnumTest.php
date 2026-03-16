<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Enums\AdminRole;
use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;

class AdminRoleEnumTest extends TestCase
{
    public function test_admin_role_enum_has_exactly_two_cases(): void
    {
        $this->assertCount(2, AdminRole::cases());
    }

    public function test_admin_case_value_is_admin(): void
    {
        $this->assertSame('admin', AdminRole::Admin->value);
    }

    public function test_super_admin_case_value_is_super_admin(): void
    {
        $this->assertSame('super_admin', AdminRole::SuperAdmin->value);
    }

    public function test_admin_role_can_be_created_from_string_value(): void
    {
        $this->assertSame(AdminRole::Admin, AdminRole::from('admin'));
        $this->assertSame(AdminRole::SuperAdmin, AdminRole::from('super_admin'));
    }
}
