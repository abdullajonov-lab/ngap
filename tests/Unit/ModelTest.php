<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Models\Admin;
use AbdullajonovLab\NutgramAdminPanel\Models\BotUser;
use AbdullajonovLab\NutgramAdminPanel\Models\Broadcast;
use AbdullajonovLab\NutgramAdminPanel\Models\Channel;
use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;

class ModelTest extends TestCase
{
    public function test_bot_user_uses_configurable_table(): void
    {
        $model = new BotUser();
        $this->assertSame('nutgram_bot_users', $model->getTable());

        config()->set('nutgram-admin-panel.table_names.bot_users', 'custom_bot_users');
        $model = new BotUser();
        $this->assertSame('custom_bot_users', $model->getTable());
    }

    public function test_channel_uses_configurable_table(): void
    {
        $model = new Channel();
        $this->assertSame('nutgram_channels', $model->getTable());

        config()->set('nutgram-admin-panel.table_names.channels', 'custom_channels');
        $model = new Channel();
        $this->assertSame('custom_channels', $model->getTable());
    }

    public function test_broadcast_uses_configurable_table(): void
    {
        $model = new Broadcast();
        $this->assertSame('nutgram_broadcasts', $model->getTable());

        config()->set('nutgram-admin-panel.table_names.broadcasts', 'custom_broadcasts');
        $model = new Broadcast();
        $this->assertSame('custom_broadcasts', $model->getTable());
    }

    public function test_admin_uses_configurable_table(): void
    {
        $model = new Admin();
        $this->assertSame('nutgram_admins', $model->getTable());

        config()->set('nutgram-admin-panel.table_names.admins', 'custom_admins');
        $model = new Admin();
        $this->assertSame('custom_admins', $model->getTable());
    }
}
