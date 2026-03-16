<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\BotUserResource;
use AbdullajonovLab\NutgramAdminPanel\Models\BotUser;
use AbdullajonovLab\NutgramAdminPanel\NutgramAdminPanelPlugin;
use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;
use Filament\Panel;
use Mockery;

class BotUserResourceTest extends TestCase
{
    public function test_resource_uses_bot_user_model(): void
    {
        $this->assertSame(BotUser::class, BotUserResource::getModel());
    }

    public function test_resource_cannot_create(): void
    {
        $this->assertFalse(BotUserResource::canCreate());
    }

    public function test_resource_has_only_index_page(): void
    {
        $pages = BotUserResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertCount(1, $pages);
    }

    public function test_resource_has_correct_navigation_icon(): void
    {
        $this->assertSame('heroicon-o-users', BotUserResource::getNavigationIcon());
    }

    public function test_plugin_registers_bot_user_resource(): void
    {
        $plugin = NutgramAdminPanelPlugin::make();

        $panel = Mockery::mock(Panel::class);
        $panel->shouldReceive('resources')
            ->once()
            ->with(Mockery::on(function (array $resources) {
                return in_array(BotUserResource::class, $resources);
            }))
            ->andReturnSelf();
        $panel->shouldReceive('pages')
            ->once()
            ->andReturnSelf();

        $plugin->register($panel);
    }

    public function test_user_label_translation_resolves(): void
    {
        $this->assertSame('Bot User', __('nutgram-admin-panel::user.label'));
    }

    public function test_user_field_translation_resolves(): void
    {
        $this->assertSame('Telegram ID', __('nutgram-admin-panel::user.fields.telegram_id'));
    }

    public function test_user_plural_label_translation_resolves(): void
    {
        $this->assertSame('Bot Users', __('nutgram-admin-panel::user.plural_label'));
    }
}
