<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\NutgramAdminPanelPlugin;
use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;
use Filament\Contracts\Plugin;

class PluginTest extends TestCase
{
    public function test_plugin_has_correct_id(): void
    {
        $plugin = NutgramAdminPanelPlugin::make();

        $this->assertSame('nutgram-admin-panel', $plugin->getId());
    }

    public function test_plugin_implements_filament_contract(): void
    {
        $plugin = NutgramAdminPanelPlugin::make();

        $this->assertInstanceOf(Plugin::class, $plugin);
    }
}
