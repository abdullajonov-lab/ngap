<?php

declare(strict_types=1);

namespace AbdullajonovLab\NutgramAdminPanel\Filament\Resources\BroadcastResource\Pages;

use AbdullajonovLab\NutgramAdminPanel\Contracts\BroadcastServiceInterface;
use AbdullajonovLab\NutgramAdminPanel\Filament\Resources\BroadcastResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBroadcast extends CreateRecord
{
    protected static string $resource = BroadcastResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(BroadcastServiceInterface::class)->createAndDispatch($data['message']);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('nutgram-admin-panel::broadcast.messages.created');
    }
}
