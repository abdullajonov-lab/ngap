<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;

class ConfigTest extends TestCase
{
    public function test_config_has_table_names(): void
    {
        $this->assertSame('nutgram_bot_users', config('nutgram-admin-panel.table_names.bot_users'));
        $this->assertSame('nutgram_channels', config('nutgram-admin-panel.table_names.channels'));
        $this->assertSame('nutgram_broadcasts', config('nutgram-admin-panel.table_names.broadcasts'));
        $this->assertSame('nutgram_admins', config('nutgram-admin-panel.table_names.admins'));
    }

    public function test_config_has_broadcast_settings(): void
    {
        $this->assertNotNull(config('nutgram-admin-panel.broadcast.queue'));
        $this->assertSame(25, config('nutgram-admin-panel.broadcast.rate_limit'));
        $this->assertSame(3, config('nutgram-admin-panel.broadcast.max_tries'));
    }

    public function test_config_has_channel_check_settings(): void
    {
        $this->assertTrue(config('nutgram-admin-panel.channel_check.enabled'));
        $this->assertSame(300, config('nutgram-admin-panel.channel_check.cache_ttl'));
    }
}
