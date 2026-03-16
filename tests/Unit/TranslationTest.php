<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;

class TranslationTest extends TestCase
{
    public function test_channel_translations_load(): void
    {
        $this->assertSame('Channel', __('nutgram-admin-panel::channel.label'));
    }

    public function test_broadcast_translations_load(): void
    {
        $this->assertSame('Broadcast', __('nutgram-admin-panel::broadcast.label'));
    }

    public function test_admin_translations_load(): void
    {
        $this->assertSame('Admin', __('nutgram-admin-panel::admin.label'));
    }

    public function test_statistics_translations_load(): void
    {
        $this->assertSame('Statistics', __('nutgram-admin-panel::statistics.label'));
    }

    public function test_general_translations_load(): void
    {
        $this->assertSame('Save', __('nutgram-admin-panel::general.labels.save'));
    }

    public function test_nested_translation_keys_resolve(): void
    {
        $this->assertSame('Chat ID', __('nutgram-admin-panel::channel.fields.chat_id'));
    }
}
