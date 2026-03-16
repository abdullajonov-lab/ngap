<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Tests\Unit;

use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\AdminResource;
use AbdullajonovLab\NutgramAdminPanel\Models\Admin;
use AbdullajonovLab\NutgramAdminPanel\NutgramAdminPanelPlugin;
use AbdullajonovLab\NutgramAdminPanel\Tests\TestCase;
use Filament\Panel;
use Mockery;

class AdminResourceTest extends TestCase
{
    public function test_admin_resource_has_correct_model(): void
    {
        $this->assertSame(Admin::class, AdminResource::getModel());
    }

    public function test_admin_resource_has_correct_navigation_icon(): void
    {
        $this->assertSame('heroicon-o-shield-check', AdminResource::getNavigationIcon());
    }

    public function test_admin_resource_has_correct_navigation_group(): void
    {
        $this->assertSame('Bot Management', AdminResource::getNavigationGroup());
    }

    public function test_admin_resource_has_form_schema(): void
    {
        $form = AdminResource::form(
            \Filament\Forms\Form::make(Mockery::mock(\Filament\Forms\Contracts\HasForms::class))
        );

        $componentNames = collect($form->getComponents(withHidden: true))
            ->map(fn ($component) => $component->getName())
            ->toArray();

        $this->assertContains('telegram_id', $componentNames);
        $this->assertContains('name', $componentNames);
        $this->assertContains('role', $componentNames);
    }

    public function test_admin_resource_has_table_columns(): void
    {
        $table = AdminResource::table(
            \Filament\Tables\Table::make(Mockery::mock(\Filament\Tables\Contracts\HasTable::class))
        );

        $columnNames = collect($table->getColumns())
            ->map(fn ($column) => $column->getName())
            ->toArray();

        $this->assertContains('telegram_id', $columnNames);
        $this->assertContains('name', $columnNames);
        $this->assertContains('role', $columnNames);
    }

    public function test_admin_resource_has_pages(): void
    {
        $pages = AdminResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('edit', $pages);
        $this->assertCount(3, $pages);
    }

    public function test_admin_resource_labels_use_translations(): void
    {
        $this->assertSame('Admin', AdminResource::getModelLabel());
        $this->assertSame('Admins', AdminResource::getPluralModelLabel());
    }

    public function test_plugin_registers_admin_resource(): void
    {
        $plugin = NutgramAdminPanelPlugin::make();

        $panel = Mockery::mock(Panel::class);
        $panel->shouldReceive('resources')
            ->once()
            ->with(Mockery::on(function (array $resources) {
                return in_array(AdminResource::class, $resources);
            }))
            ->andReturnSelf();
        $panel->shouldReceive('pages')
            ->once()
            ->andReturnSelf();

        $plugin->register($panel);
    }

    public function test_table_has_delete_action(): void
    {
        $table = AdminResource::table(
            \Filament\Tables\Table::make(Mockery::mock(\Filament\Tables\Contracts\HasTable::class))
        );

        $actionNames = collect($table->getActions())
            ->map(fn ($action) => $action->getName())
            ->toArray();

        $this->assertContains('delete', $actionNames);
    }
}
