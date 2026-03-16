<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;
use Illuminate\Support\Facades\Schema;

class MigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    public function test_bot_users_table_is_created(): void
    {
        $table = config('nutgram-admin-panel.table_names.bot_users');

        $this->assertTrue(Schema::hasTable($table));
        $this->assertTrue(Schema::hasColumns($table, [
            'telegram_id',
            'first_name',
            'last_name',
            'username',
            'language_code',
            'status',
            'last_activity_at',
        ]));
    }

    public function test_channels_table_is_created(): void
    {
        $table = config('nutgram-admin-panel.table_names.channels');

        $this->assertTrue(Schema::hasTable($table));
        $this->assertTrue(Schema::hasColumns($table, [
            'chat_id',
            'title',
            'username',
            'is_mandatory',
            'is_active',
            'member_count',
        ]));
    }

    public function test_broadcasts_table_is_created(): void
    {
        $table = config('nutgram-admin-panel.table_names.broadcasts');

        $this->assertTrue(Schema::hasTable($table));
        $this->assertTrue(Schema::hasColumns($table, [
            'message',
            'parse_mode',
            'status',
            'total_users',
            'sent_count',
            'failed_count',
            'blocked_count',
            'started_at',
            'completed_at',
        ]));
    }

    public function test_admins_table_is_created(): void
    {
        $table = config('nutgram-admin-panel.table_names.admins');

        $this->assertTrue(Schema::hasTable($table));
        $this->assertTrue(Schema::hasColumns($table, [
            'telegram_id',
            'name',
            'role',
        ]));
    }
}
