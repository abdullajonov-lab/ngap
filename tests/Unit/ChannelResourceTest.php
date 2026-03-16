<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\ChannelResource;
use AbdullajonovLab\NutgramAdminPanel\Models\Channel;
use AbdullajonovLab\NutgramAdminPanel\NutgramAdminPanelPlugin;
use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;
use Filament\Panel;
use Mockery;

class ChannelResourceTest extends TestCase
{
    public function test_channel_resource_has_correct_model(): void
    {
        $this->assertSame(Channel::class, ChannelResource::getModel());
    }

    public function test_channel_resource_has_correct_navigation_icon(): void
    {
        $this->assertSame('heroicon-o-megaphone', ChannelResource::getNavigationIcon());
    }

    public function test_channel_resource_has_correct_navigation_group(): void
    {
        $this->assertSame('Bot Management', ChannelResource::getNavigationGroup());
    }

    public function test_channel_resource_has_form_schema(): void
    {
        $form = ChannelResource::form(
            \Filament\Forms\Form::make(Mockery::mock(\Filament\Forms\Contracts\HasForms::class))
        );

        $componentNames = collect($form->getComponents(withHidden: true))
            ->map(fn ($component) => $component->getName())
            ->toArray();

        $this->assertContains('chat_id', $componentNames);
        $this->assertContains('is_mandatory', $componentNames);
        $this->assertContains('is_active', $componentNames);
    }

    public function test_channel_resource_has_table_columns(): void
    {
        $table = ChannelResource::table(
            \Filament\Tables\Table::make(Mockery::mock(\Filament\Tables\Contracts\HasTable::class))
        );

        $columnNames = collect($table->getColumns())
            ->map(fn ($column) => $column->getName())
            ->toArray();

        $this->assertContains('chat_id', $columnNames);
        $this->assertContains('title', $columnNames);
        $this->assertContains('username', $columnNames);
        $this->assertContains('is_mandatory', $columnNames);
        $this->assertContains('is_active', $columnNames);
        $this->assertContains('member_count', $columnNames);
    }

    public function test_channel_resource_has_pages(): void
    {
        $pages = ChannelResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('edit', $pages);
        $this->assertCount(3, $pages);
    }

    public function test_channel_resource_labels_use_translations(): void
    {
        $this->assertSame('Channel', ChannelResource::getModelLabel());
        $this->assertSame('Channels', ChannelResource::getPluralModelLabel());
    }

    public function test_plugin_registers_channel_resource(): void
    {
        $plugin = NutgramAdminPanelPlugin::make();

        $panel = Mockery::mock(Panel::class);
        $panel->shouldReceive('resources')
            ->once()
            ->with(Mockery::on(function (array $resources) {
                return in_array(ChannelResource::class, $resources);
            }))
            ->andReturnSelf();
        $panel->shouldReceive('pages')
            ->once()
            ->andReturnSelf();

        $plugin->register($panel);
    }
}
