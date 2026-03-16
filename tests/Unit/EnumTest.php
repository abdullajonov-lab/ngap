<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Enums\BroadcastStatus;
use AbdullajonovLab\NutgramAdminPanel\Enums\UserStatus;
use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;

class EnumTest extends TestCase
{
    public function test_broadcast_status_has_all_cases(): void
    {
        $cases = BroadcastStatus::cases();

        $this->assertCount(5, $cases);

        $values = array_map(fn (BroadcastStatus $case) => $case->value, $cases);

        $this->assertContains('pending', $values);
        $this->assertContains('sending', $values);
        $this->assertContains('completed', $values);
        $this->assertContains('cancelled', $values);
        $this->assertContains('failed', $values);
    }

    public function test_user_status_has_all_cases(): void
    {
        $cases = UserStatus::cases();

        $this->assertCount(3, $cases);

        $values = array_map(fn (UserStatus $case) => $case->value, $cases);

        $this->assertContains('active', $values);
        $this->assertContains('blocked', $values);
        $this->assertContains('deactivated', $values);
    }

    public function test_broadcast_status_from_string(): void
    {
        $this->assertSame(BroadcastStatus::Pending, BroadcastStatus::from('pending'));
    }

    public function test_user_status_from_string(): void
    {
        $this->assertSame(UserStatus::Active, UserStatus::from('active'));
    }
}
