<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Filament\Resources\AdminResource\Pages;

use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\AdminResource;
use AbdullajonovLab\NutgramAdminPanel\Models\Admin;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action) {
                    if (Admin::count() <= 1) {
                        Notification::make()
                            ->danger()
                            ->title(__('nutgram-admin-panel::admin.messages.last_admin_warning'))
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }
}
