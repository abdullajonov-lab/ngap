<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\BroadcastResource;
use AbdullajonovLab\NutgramAdminPanel\Models\Broadcast;
use AbdullajonovLab\NutgramAdminPanel\NutgramAdminPanelPlugin;
use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;
use Filament\Panel;
use Mockery;

class BroadcastResourceTest extends TestCase
{
    public function test_broadcast_resource_has_correct_model(): void
    {
        $this->assertSame(Broadcast::class, BroadcastResource::getModel());
    }

    public function test_broadcast_resource_has_correct_navigation_icon(): void
    {
        $this->assertSame('heroicon-o-paper-airplane', BroadcastResource::getNavigationIcon());
    }

    public function test_broadcast_resource_has_correct_navigation_group(): void
    {
        $this->assertSame('Bot Management', BroadcastResource::getNavigationGroup());
    }

    public function test_broadcast_resource_has_form_schema(): void
    {
        $form = BroadcastResource::form(
            \Filament\Forms\Form::make(Mockery::mock(\Filament\Forms\Contracts\HasForms::class))
        );

        $componentNames = collect($form->getComponents(withHidden: true))
            ->map(fn ($component) => $component->getName())
            ->toArray();

        $this->assertContains('message', $componentNames);
    }

    public function test_broadcast_resource_has_table_columns(): void
    {
        $table = BroadcastResource::table(
            \Filament\Tables\Table::make(Mockery::mock(\Filament\Tables\Contracts\HasTable::class))
        );

        $columnNames = collect($table->getColumns())
            ->map(fn ($column) => $column->getName())
            ->toArray();

        $this->assertContains('message', $columnNames);
        $this->assertContains('status', $columnNames);
        $this->assertContains('total_users', $columnNames);
        $this->assertContains('sent_count', $columnNames);
        $this->assertContains('failed_count', $columnNames);
        $this->assertContains('blocked_count', $columnNames);
        $this->assertContains('created_at', $columnNames);
    }

    public function test_broadcast_resource_table_has_cancel_action(): void
    {
        $table = BroadcastResource::table(
            \Filament\Tables\Table::make(Mockery::mock(\Filament\Tables\Contracts\HasTable::class))
        );

        $actionNames = collect($table->getActions())
            ->map(fn ($action) => $action->getName())
            ->toArray();

        $this->assertContains('cancel', $actionNames);
    }

    public function test_broadcast_resource_has_pages(): void
    {
        $pages = BroadcastResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayNotHasKey('edit', $pages);
        $this->assertCount(2, $pages);
    }

    public function test_broadcast_resource_labels_use_translations(): void
    {
        $this->assertSame('Broadcast', BroadcastResource::getModelLabel());
        $this->assertSame('Broadcasts', BroadcastResource::getPluralModelLabel());
    }

    public function test_broadcast_resource_table_has_polling(): void
    {
        $table = BroadcastResource::table(
            \Filament\Tables\Table::make(Mockery::mock(\Filament\Tables\Contracts\HasTable::class))
        );

        $this->assertSame('5s', $table->getPollingInterval());
    }

    public function test_broadcast_resource_cannot_be_edited(): void
    {
        $this->assertFalse(BroadcastResource::canEdit(new Broadcast()));
    }

    public function test_plugin_registers_broadcast_resource(): void
    {
        $plugin = NutgramAdminPanelPlugin::make();

        $panel = Mockery::mock(Panel::class);
        $panel->shouldReceive('resources')
            ->once()
            ->with(Mockery::on(function (array $resources) {
                return in_array(BroadcastResource::class, $resources);
            }))
            ->andReturnSelf();
        $panel->shouldReceive('pages')
            ->once()
            ->andReturnSelf();

        $plugin->register($panel);
    }
}
